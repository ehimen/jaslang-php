<?php

namespace Ehimen\Jaslang\Parser;

use Ehimen\Jaslang\Ast\BinaryOperation;
use Ehimen\Jaslang\Ast\BooleanLiteral;
use Ehimen\Jaslang\Ast\FunctionCall;
use Ehimen\Jaslang\Ast\Literal;
use Ehimen\Jaslang\Ast\Node;
use Ehimen\Jaslang\Ast\NumberLiteral;
use Ehimen\Jaslang\Ast\ParentNode;
use Ehimen\Jaslang\Ast\StringLiteral;
use Ehimen\Jaslang\Exception\RuntimeException;
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

    /**
     * @var Node[]
     */
    private $nodeStack = [];
    
    private $previousNode;
    
    private $currentToken;
    
    private $ast;
    
    private $input;
    
    private $nextToken;

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
    }

    public function parse($input)
    {
        $this->input = $input;
        $dfa = $this->getDfa();

        $tokens = array_values(array_filter(
            $this->lexer->tokenize($input),
            function ($token) {
                return ($token['type'] !== Lexer::TOKEN_WHITESPACE);
            }
        ));
        
        foreach ($tokens as $i => $token) {
            $this->currentToken = $token;
            $this->nextToken    = isset($tokens[$i + 1]) ? $tokens[$i + 1] : null;
            
            try {
                $dfa->transition($token['type']);
            } catch (TransitionImpossibleException $e) {
                throw new UnexpectedTokenException($input, $token);
            }
        }
        
        $this->previousNode = null;
        
        if (!empty($this->nodeStack)) {
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
         
        $createNode = function () {
            $this->createNode();
        };
        
        $closeNode = function() {
            if (empty($this->nodeStack)) {
                // We've been asked to close a node that doesn't exist.
                // This means we're closing too many functions, e.g.
                // foo())
                throw new UnexpectedTokenException($this->input, $this->currentToken);
            }
            
            array_pop($this->nodeStack);
        };

        $literalTokens = [Lexer::TOKEN_STRING, Lexer::TOKEN_NUMBER, Lexer::TOKEN_BOOLEAN];
        
        $builder
            ->addRule(0, Lexer::TOKEN_IDENTIFIER, 'identifier', $createNode)
            ->addRule(0, $literalTokens, 'literal', $createNode)
            ->addRule('literal', Lexer::TOKEN_OPERATOR, 'operator', $createNode)
            ->addRule('operator', Lexer::TOKEN_IDENTIFIER, 'identifier', $createNode)
            ->addRule('operator', $literalTokens, 'literal', $createNode)
            ->addRule('identifier', Lexer::TOKEN_LEFT_PAREN, 'fn-open')
            ->addRule('fn-open', Lexer::TOKEN_IDENTIFIER, 'fn-identifier', $createNode)
            ->addRule('fn-open', $literalTokens, 'fn-literal', $createNode)
            ->addRule('fn-open', Lexer::TOKEN_RIGHT_PAREN, 'fn-close', $closeNode)
            ->addRule('fn-identifier', Lexer::TOKEN_LEFT_PAREN, 'fn-open')
            ->addRule('fn-literal', Lexer::TOKEN_OPERATOR, 'fn-operator', $createNode)
            ->addRule('fn-literal', Lexer::TOKEN_COMMA, 'fn-comma')
            ->addRule('fn-literal', Lexer::TOKEN_RIGHT_PAREN, 'fn-close', $closeNode)
            ->addRule('fn-close', Lexer::TOKEN_COMMA, 'fn-comma')
            ->addRule('fn-close', Lexer::TOKEN_RIGHT_PAREN, 'fn-close', $closeNode)
            ->addRule('fn-close', Lexer::TOKEN_OPERATOR, 'operator')
            ->addRule('fn-comma', $literalTokens, 'fn-literal', $createNode)
            ->addRule('fn-comma', Lexer::TOKEN_IDENTIFIER, 'fn-identifier', $createNode)
            ->addRule('fn-operator', Lexer::TOKEN_IDENTIFIER, 'fn-identifier', $createNode)
            ->addRule('fn-operator', $literalTokens, 'fn-literal', $createNode)
            
            ->start(0)
            ->accept('literal')
            ->accept('fn-close')
        ;
        
        return $builder->build();
    }

    private function createNode()
    {
        if (Lexer::TOKEN_STRING === $this->currentToken['type']) {
            $node = new StringLiteral($this->currentToken['value']);
        } elseif (Lexer::TOKEN_NUMBER === $this->currentToken['type']) {
            $node = new NumberLiteral($this->currentToken['value']);
        } elseif (Lexer::TOKEN_BOOLEAN === $this->currentToken['type']) {
            $node = new BooleanLiteral($this->currentToken['value']);
        } elseif ($this->currentToken['type'] === Lexer::TOKEN_IDENTIFIER) {
            $node = new FunctionCall($this->currentToken['value'], []);
        } elseif ($this->currentToken['type'] === Lexer::TOKEN_OPERATOR) {
            if (!$this->previousNode) {
                // TODO: evaluation exception?
                throw new RuntimeException();
            }
            
            $node = new BinaryOperation($this->currentToken['value'], $this->previousNode);
        } else {
            throw new RuntimeException('Unhandled type "' . $this->currentToken['type'] . '" in Jaslang parser');
        }
        
        $outerNode = end($this->nodeStack);
        
        if (($outerNode instanceof ParentNode) && (Lexer::TOKEN_OPERATOR !== $this->nextToken['type'])) {
            // Add this to the current function, unless it's going to
            // be consumed by a subsequent binary operator.
            $outerNode->addChild($node);
            
            if ($outerNode instanceof BinaryOperation) {
                array_pop($this->nodeStack);
            }
        }
        
        if ($node instanceof ParentNode) {
            array_push($this->nodeStack, $node);
        }
        
        // Mark the tree we return.
        // If nothing set, use the first thing we've built.
        // If it is set and is a literal, and we've just built a binary
        // operator, take that place as infix operators are a pain :(
        if (!$this->ast || (($this->ast instanceof Literal) && ($node instanceof BinaryOperation))) {
            $this->ast = $node;
        }
        
        $this->previousNode = $node;
    }
}
