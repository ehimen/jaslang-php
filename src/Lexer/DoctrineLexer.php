<?php

namespace Ehimen\Jaslang\Lexer;

use Doctrine\Common\Lexer\AbstractLexer;
use Ehimen\Jaslang\Parser\Exception\UnexpectedEndOfInputException;

/**
 * TODO: this should wrap Doctrine lexer, not extend it.
 */
class DoctrineLexer implements Lexer
{
    const DTYPE_OTHER = 0;
    const DTYPE_PAREN_LEFT  = 1;
    const DTYPE_PAREN_RIGHT = 2;
    const DTYPE_QUOTE_SINGLE = 3;
    const DTYPE_QUOTE_DOUBLE = 4;
    const DTYPE_BACKSLASH = 5;
    const DTYPE_COMMA = 6;
    const DTYPE_WHITESPACE = 7;
    const DTYPE_OPERATOR = 8;
    const DTYPE_SEMICOLON = 9;
    const DTYPE_LITERAL = 10;
    
    private $currentQuote = null;
    private $currentToken = '';
    private $currentPosition = 0;
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

    public static function isLiteral(array $token)
    {
        return in_array($token['type'], Lexer::LITERAL_TOKENS, true);
    }

    public function tokenize($input)
    {
        $this->resetState();
        
        $updatePosition = true;
        $tokens         = $this->scan($input);
        
        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            
            if ($updatePosition) {
                $this->currentPosition = $token['position'];
            }

            $updatePosition = true;
            $value          = $token['value'];
            $type           = $token['type'];
            $inQuotes       = is_string($this->currentQuote);

            // Handle escaping.
            if ($inQuotes && $value === Lexer::ESCAPE_CHAR) {
                $nextValue  = isset($tokens[$i + 1]) ? $tokens[$i + 1]['value'] : null;
                if (in_array($nextValue, Lexer::ESCAPABLE_CHARS, true)) {
                    $updatePosition = false;
                    $this->currentToken .= $nextValue;
                    $i++;
                    continue;
                }
            }

            if ($this->currentQuote === $value) {
                $this->token(Lexer::TOKEN_LITERAL_STRING);
                continue;
            }

            if (!$inQuotes && ((static::DTYPE_QUOTE_DOUBLE === $type) || (static::DTYPE_QUOTE_SINGLE === $type))) {
                $this->currentQuote = $value;
                $updatePosition = false;
                continue;
            }

            $this->currentToken .= $value;

            if (!$inQuotes) {
                if ($type === static::DTYPE_PAREN_LEFT) {
                    $this->token(Lexer::TOKEN_LEFT_PAREN);
                } elseif ($type === static::DTYPE_PAREN_RIGHT) {
                    $this->token(Lexer::TOKEN_RIGHT_PAREN);
                } elseif ($type === static::DTYPE_COMMA) {
                    $this->token(Lexer::TOKEN_COMMA);
                } elseif ($type === static::DTYPE_WHITESPACE) {
                    $this->token(Lexer::TOKEN_WHITESPACE);
                } elseif ($type === static::DTYPE_BACKSLASH) {
                    $this->token(Lexer::TOKEN_BACKSLASH);
                } elseif ($type === static::DTYPE_SEMICOLON) {
                    $this->token(Lexer::TOKEN_STATETERM);
                } elseif ($type === static::DTYPE_OPERATOR) {
                    $this->token(Lexer::TOKEN_OPERATOR);
                } elseif ($type === static::DTYPE_LITERAL) {
                    $this->token(Lexer::TOKEN_LITERAL);
                } elseif (ctype_alpha($value[0])) {     // If starting with a letter, it's an identifier.
                    $this->token(Lexer::TOKEN_IDENTIFIER);
                } else {
                    $this->token(Lexer::TOKEN_UNKNOWN);
                }
            } else {
                // We're continuing a token, so leave the position marker where it was.
                $updatePosition = false;
            }
        }
        
        if (is_string($this->currentQuote)) {
            throw new UnexpectedEndOfInputException($input);
        }
        
        return $this->jaslangTokens;
    }

    private function resetState()
    {
        $this->currentQuote = null;
        $this->currentToken = '';
        $this->currentPosition = 0;
        $this->jaslangTokens = [];
    }

    private function token($type)
    {
        if (strlen($this->currentToken) === 0) {
            return;
        }
        
        $this->jaslangTokens[] = [
            'type'     => $type,
            'value'    => $this->currentToken,
            'position' => $this->currentPosition + 1, // Doctrine position is 0-indexed, we want first char to be at 1.
        ];
        
        $this->currentToken = '';
        $this->currentQuote = null;
    }

    /**
     * {@inheritdoc}
     */
    private function scan($input)
    {
        $tokens = [];
        $regex  = sprintf(
            '/(%s)|/u',
            implode(')|(', $this->getCatchablePatterns())
        );
        
        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        $matches = preg_split($regex, $input, -1, $flags);
        
        $position = 0;
        
        foreach ($matches as $match) {
            $type = $this->getType($match[0]);
            
            $tokens[] = array(
                'value'    => $match[0],
                'type'     => $type,
                'position' => $position,
            );
            
            $position += mb_strlen($match[0]);
        }
        
        return $tokens;
    }

    protected function getCatchablePatterns()
    {
        $patterns = array_merge(
            // Type-driven literal capture.
            $this->literals,
            // User defined operators.
            array_map(
                function ($operator) {
                    return preg_quote($operator, '/');
                },
                $this->operators
            ),
            [
                '\w+',        // Group all word characters
                '\s+',        // And group all continuous whitespace
            ]
        );
        
        return $patterns;
    }

    protected function getNonCatchablePatterns()
    {
        return [];
    }

    protected function getType($value)
    {
        if ('\\' === $value) {
            return static::DTYPE_BACKSLASH;
        } elseif ('(' === $value) {
            return static::DTYPE_PAREN_LEFT;
        } elseif (')' === $value) {
            return static::DTYPE_PAREN_RIGHT;
        } elseif ('\'' === $value) {
            return static::DTYPE_QUOTE_SINGLE;
        } elseif ('"' === $value) {
            return static::DTYPE_QUOTE_DOUBLE;
        } elseif (',' === $value) {
            return static::DTYPE_COMMA;
        } elseif (';' === $value) {
            return static::DTYPE_SEMICOLON;
        } elseif ('' === trim($value)) {
            return static::DTYPE_WHITESPACE;
        } elseif ($this->matchesCustomLiteral($value)) {
            return static::DTYPE_LITERAL;
        } elseif (in_array($value, $this->operators, true)) {
            return static::DTYPE_OPERATOR;
        }
        
        return static::DTYPE_OTHER;
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
}
