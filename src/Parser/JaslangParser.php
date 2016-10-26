<?php

namespace Ehimen\Jaslang\Parser;

use Ehimen\Jaslang\Ast\BinaryOperation;
use Ehimen\Jaslang\Ast\BooleanLiteral;
use Ehimen\Jaslang\Ast\FunctionCall;
use Ehimen\Jaslang\Ast\NumberLiteral;
use Ehimen\Jaslang\Ast\ParentNode;
use Ehimen\Jaslang\Ast\Root;
use Ehimen\Jaslang\Ast\StringLiteral;
use Ehimen\Jaslang\Exception\RuntimeException;
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
     * @var ParentNode[]
     */
    private $nodeStack = [];
    
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

        $this->ast = new Root();
        $this->nodeStack = [$this->ast];
        
        foreach ($tokens as $i => $token) {
            $this->currentToken = $token;
            $this->nextToken    = isset($tokens[$i + 1]) ? $tokens[$i + 1] : null;
            
            try {
                $dfa->transition($token['type']);
            } catch (TransitionImpossibleException $e) {
                throw new UnexpectedTokenException($input, $token);
            }
        }

        if (count($this->nodeStack) !== 1) {
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
            if (count($this->nodeStack) <= 1) {
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
            ->addRule('fn-close', Lexer::TOKEN_OPERATOR, 'fn-operator', $createNode)
            ->addRule('fn-comma', $literalTokens, 'fn-literal', $createNode)
            ->addRule('fn-comma', Lexer::TOKEN_IDENTIFIER, 'fn-identifier', $createNode)
            ->addRule('fn-operator', Lexer::TOKEN_IDENTIFIER, 'fn-identifier', $createNode)
            ->addRule('fn-operator', $literalTokens, 'fn-literal', $createNode)
            
            ->start(0)
            ->accept('literal')
            ->accept('fn-literal')
            ->accept('fn-close')
        ;
        
        return $builder->build();
    }

    private function createNode()
    {
        // Context is the parent node we want to add to.
        $context = end($this->nodeStack);

        if (Lexer::TOKEN_STRING === $this->currentToken['type']) {
            $node = new StringLiteral($this->currentToken['value']);
        } elseif (Lexer::TOKEN_NUMBER === $this->currentToken['type']) {
            $node = new NumberLiteral($this->currentToken['value']);
        } elseif (Lexer::TOKEN_BOOLEAN === $this->currentToken['type']) {
            $node = new BooleanLiteral($this->currentToken['value']);
        } elseif ($this->currentToken['type'] === Lexer::TOKEN_IDENTIFIER) {
            $node = new FunctionCall($this->currentToken['value']);
        } elseif ($this->currentToken['type'] === Lexer::TOKEN_OPERATOR) {
            $node = new BinaryOperation($this->currentToken['value'], $context->getLastChild());
        } else {
            throw new RuntimeException('Unhandled type "' . $this->currentToken['type'] . '" in Jaslang parser');
        }
        
        if ($context instanceof ParentNode) {
            // If we're creating a binary node, its infix nature
            // means we need to shift the AST a bit.
            // We take the place of the most recently added
            // node in our context. We ensure our operator takes
            // what we're replacing as its LHS (done on construction
            // above). If it's not a binary operation, we simply add
            // it to the end of the parent's children.
            $context->addChild($node, ($node instanceof BinaryOperation));

            if ($context instanceof BinaryOperation) {
                // If we're in a binary operator, this is the second argument so we
                // are closing it.
                array_pop($this->nodeStack);
            }
        }

        if ($node instanceof ParentNode) {
            // If we've created a node that accepts children,
            // push us on to the stack for future iterations.
            array_push($this->nodeStack, $node);
        }
    }
}
