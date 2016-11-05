<?php

namespace Ehimen\Jaslang\Engine\Lexer;

use Ehimen\Jaslang\Engine\Parser\Exception\UnexpectedEndOfInputException;

/**
 * The core Jaslang Lexer implementation.
 */
class JaslangLexer implements Lexer
{
    private $currentQuote = null;
    private $currentToken = '';
    private $currentTokenPosition = 1;
    private $jaslangTokens = [];
    
    /**
     * @var string[]
     */
    private $operators = [];
    
    /**
     * @var string[]
     */
    private $literals = [];

    /**
     * $operators and $literals allow for customisation of a language.
     * 
     * @param string[] $operators Any exact strings the lexer should consider an operator. 
     * @param string[] $literals Any patterns the lexer should consider a literal.
     */
    public function __construct(array $operators = [], array $literals = [])
    {
        $this->operators = $operators;
        $this->literals  = $literals;
    }

    public function tokenize($input)
    {
        $this->resetState();
        
        $updatePosition = true;
        $matches        = $this->splitInput($input);
        $position       = 1;
        
        for ($i = 0; $i < count($matches); $i++) {
            if ($updatePosition) {
                $this->currentTokenPosition = $position;
            }
            
            $value          = $matches[$i];
            $updatePosition = true;
            $inQuotes       = is_string($this->currentQuote);
            
            // Update a running position as preg_split offset capture breaks with multibyte.
            $position += mb_strlen($value);

            if ($inQuotes && $value === Lexer::ESCAPE_CHAR) {
                // Handle escaping if in a string.
                // If this is an escape character and the next is escapable,
                // ignore the current escape character, add the next
                // character to the current string, and bump along to the
                // next match.
                $nextValue  = isset($matches[$i + 1]) ? $matches[$i + 1] : null;
                
                if (in_array($nextValue, Lexer::ESCAPABLE_CHARS, true)) {
                    $updatePosition = false;
                    $this->currentToken .= $nextValue;
                    $i++;
                    continue;
                }
            }

            if ($this->currentQuote === $value) {
                // Terminating a string.
                $this->token(Lexer::TOKEN_LITERAL_STRING);
                continue;
            }

            if (!$inQuotes && (('"' === $value) || ("'" === $value))) {
                // Starting a string.
                $this->currentQuote = $value;
                $updatePosition = false;
                continue;
            }

            $this->currentToken .= $value;

            if (!$inQuotes) {
                $this->tokenizeUnquoted($value);
            } else {
                // We're continuing a token, so leave the position marker where it was.
                $updatePosition = false;
            }
        }
        
        if (is_string($this->currentQuote)) {
            // If our current quote is still set,
            // we must have a non-terminated string.
            throw new UnexpectedEndOfInputException($input);
        }
        
        return $this->jaslangTokens;
    }

    private function resetState()
    {
        $this->currentQuote = null;
        $this->currentToken = '';
        $this->currentTokenPosition = 0;
        $this->jaslangTokens = [];
    }

    private function token($type)
    {
        if (strlen($this->currentToken) === 0) {
            return;
        }
        
        $this->jaslangTokens[] = new Token($this->currentToken, $type, $this->currentTokenPosition);
        
        $this->currentToken = '';
        $this->currentQuote = null;
    }

    private function matchesCustomLiteral($value)
    {
        foreach ($this->literals as $pattern) {
            if (preg_match('/' . $pattern . '/', $value)) {
                return true;
            }
        }
        
        return false;
    }

    private function splitInput($input)
    {
        $dynamicPatterns = array_merge(
            // Type-driven literal capture.
            $this->literals,
            // User defined operators.
            array_map(
                function ($operator) {
                    return preg_quote($operator, '/');
                },
                $this->operators
            )
        );
        
        $regex  = sprintf(
            '/(%s)|(\w+)|(\s+)|/u',     // Group all word characters & group all continuous whitespace.
            implode(')|(', $dynamicPatterns)
        );
        
        return preg_split(
            $regex,
            $input,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );
    }

    private function matchesCustomOperator($value)
    {
        return in_array($value, $this->operators, true);
    }

    private function tokenizeUnquoted($value)
    {
        if ('(' === $value) {
            $this->token(Lexer::TOKEN_LEFT_PAREN);
        } elseif (')' === $value) {
            $this->token(Lexer::TOKEN_RIGHT_PAREN);
        } elseif (',' === $value) {
            $this->token(Lexer::TOKEN_COMMA);
        } elseif ('' === trim($value)) {
            $this->token(Lexer::TOKEN_WHITESPACE);
        } elseif ('\\' === $value) {
            $this->token(Lexer::TOKEN_BACKSLASH);
        } elseif (';' === $value) {
            $this->token(Lexer::TOKEN_STATETERM);
        } elseif ($this->matchesCustomOperator($value)) {
            $this->token(Lexer::TOKEN_OPERATOR);
        } elseif ($this->matchesCustomLiteral($value)) {
            $this->token(Lexer::TOKEN_LITERAL);
        } elseif (ctype_alpha($value[0])) {     // If starting with a letter, it's an identifier.
            $this->token(Lexer::TOKEN_IDENTIFIER);
        } else {
            $this->token(Lexer::TOKEN_UNKNOWN);
        }
    }
}
