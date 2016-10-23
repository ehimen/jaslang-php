<?php

namespace Ehimen\Jaslang\Parser;

use Ehimen\Jaslang\Ast\FunctionCall;
use Ehimen\Jaslang\Ast\NumberLiteral;
use Ehimen\Jaslang\Ast\StringLiteral;
use Ehimen\Jaslang\Lexer\DoctrineLexer;
use Ehimen\Jaslang\Lexer\Lexer;
use Ehimen\Jaslang\Parser\Dfa\DfaBuilder;
use Ehimen\Jaslang\Parser\Dfa\Exception\NotAcceptedException;
use Ehimen\Jaslang\Parser\Dfa\Exception\TransitionImpossibleException;
use Ehimen\Jaslang\Parser\Exception\UnexpectedEndOfInputException;
use Ehimen\Jaslang\Parser\Exception\UnexpectedTokenException;

/**
 */
class JaslangParser implements Parser
{
    /**
     * @var Lexer
     */
    private $lexer;
    
    private $functionStack = [];
    
    private $currentToken;
    
    private $ast;
    
    private $input;
    
    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
    }

    public static function createDefault()
    {
        return new static(new DoctrineLexer());
    }

    public function parse($input)
    {
        $this->input = $input;
        $dfa = $this->getDfa();
        
        foreach ($this->lexer->tokenize($input) as $token) {
            $this->currentToken = $token;
            
            try {
                $dfa->transition($token['type']);
            } catch (TransitionImpossibleException $e) {
                throw new UnexpectedTokenException($input, $token);
            }
        }
        
        if (!empty($this->functionStack)) {
            // Not all function calls were terminated.
            throw new UnexpectedEndOfInputException($input);
        }
        
        try {
            $dfa->accept();
        } catch (NotAcceptedException $e) {
            throw new UnexpectedEndOfInputException($input);
        }
        
        return $this->ast;
    }

    private function getDfa()
    {
        $builder = new DfaBuilder();
        
        $closeFunction = function () {
            $currentFunction = array_pop($this->functionStack);
            $outerFunction   = end($this->functionStack);

            if (!($currentFunction instanceof FunctionCall)) {
                throw new UnexpectedTokenException($this->input, $this->currentToken);
            }
            
            if ($outerFunction instanceof FunctionCall) {
                $outerFunction->addArgument($currentFunction);
            }
            
            if (empty($this->functionStack)) {
                $this->ast = $currentFunction;
            }
        };
        
        $openFunction = function () {
            $this->functionStack[] = $currentFunction = new FunctionCall($this->currentToken['value'], []);
        };
        
        $addLiteral = function () {
            if (Lexer::TOKEN_STRING === $this->currentToken['type']) {
                $literal = new StringLiteral($this->currentToken['value']);
            } elseif (Lexer::TOKEN_UNQUOTED === $this->currentToken['type']) {
                $literal = new NumberLiteral($this->currentToken['value']);
            } else {
                throw new \RuntimeException('Not supported token type', $this->currentToken['type']);
            }
            
            $currentFunction = end($this->functionStack);
            
            if ($currentFunction instanceof FunctionCall) {
                $currentFunction->addArgument($literal);
            } else {
                $this->ast = $literal;
            }
        };
        
        $builder
            ->addRule(0, Lexer::TOKEN_IDENTIFIER, 'fn-start')
            ->addRule(0, Lexer::TOKEN_WHITESPACE, 0)
            ->addRule(0, [Lexer::TOKEN_STRING, Lexer::TOKEN_UNQUOTED], 1)
            ->addRule('fn-start', Lexer::TOKEN_LEFT_PAREN, 'fn-arg-list-start')
            ->addRule('fn-start', Lexer::TOKEN_WHITESPACE, 'fn-start')
            ->addRule('fn-arg-list-start', [Lexer::TOKEN_UNQUOTED, Lexer::TOKEN_STRING], 'fn-arg-term')
            ->addRule('fn-arg-list-start', Lexer::TOKEN_WHITESPACE, 'fn-arg-list-start')
            ->addRule('fn-arg-list-start', Lexer::TOKEN_IDENTIFIER, 'fn-start')
            ->addRule('fn-arg-list-start', Lexer::TOKEN_RIGHT_PAREN, 'fn-arg-closed')
            ->addRule('fn-arg-term', Lexer::TOKEN_COMMA, 'fn-arg-list-mid')
            ->addRule('fn-arg-term', Lexer::TOKEN_WHITESPACE, 'fn-arg-list-term')
            ->addRule('fn-arg-term', Lexer::TOKEN_RIGHT_PAREN, 'fn-arg-closed')
            ->addRule('fn-arg-list-mid', [Lexer::TOKEN_UNQUOTED, Lexer::TOKEN_STRING], 'fn-arg-term')
            ->addRule('fn-arg-list-mid', Lexer::TOKEN_WHITESPACE, 'fn-arg-list-mid')
            ->addRule('fn-arg-list-mid', Lexer::TOKEN_IDENTIFIER, 'fn-start')
            ->addRule('fn-arg-closed', Lexer::TOKEN_COMMA, 'fn-arg-list-mid')
            ->addRule('fn-arg-closed', [Lexer::TOKEN_WHITESPACE, Lexer::TOKEN_RIGHT_PAREN], 'fn-arg-closed')
            ->addRule(1, Lexer::TOKEN_WHITESPACE, 1)
            
            ->whenEntering('fn-start', Lexer::TOKEN_IDENTIFIER, $openFunction)
            ->whenEntering('fn-arg-term', [Lexer::TOKEN_UNQUOTED, Lexer::TOKEN_STRING], $addLiteral)
            ->whenEntering(['fn-arg-closed', 'fn-arg-list-mid'], Lexer::TOKEN_RIGHT_PAREN, $closeFunction)
            ->whenEntering(1, [Lexer::TOKEN_STRING, Lexer::TOKEN_UNQUOTED], $addLiteral)    // TODO, merge with fn-arg-term?
            
            ->start(0)
            ->accept(1)
            ->accept('fn-arg-closed');
        
        return $builder->build();
    }
}