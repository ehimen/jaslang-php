<?php

namespace Ehimen\Jaslang\Parser;

use Ehimen\Jaslang\Ast\BinaryOperation;
use Ehimen\Jaslang\Ast\BooleanLiteral;
use Ehimen\Jaslang\Ast\Container;
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
            $this->closeNode();
        };

        $literalTokens = [Lexer::TOKEN_STRING, Lexer::TOKEN_NUMBER, Lexer::TOKEN_BOOLEAN];

        $literal = 'literal';
        $operator = 'operator';
        $identifier = 'identifier';
        $fnOpen = 'fn-open';
        $parenOpen = 'paren-open';
        $parenClose = 'paren-close';
        $comma = 'comma';
        $stateTerm = 'comma';
        $builder
            ->addRule(0,           Lexer::TOKEN_IDENTIFIER,  $identifier)
            ->addRule(0,           $literalTokens,           $literal)
            ->addRule(0,           Lexer::TOKEN_LEFT_PAREN,  $parenOpen)
            ->addRule($literal,    Lexer::TOKEN_OPERATOR,    $operator)
            ->addRule($literal,    Lexer::TOKEN_COMMA,       $comma)
            ->addRule($literal,    Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($literal,    Lexer::TOKEN_STATETERM,   $stateTerm)
            ->addRule($operator,   Lexer::TOKEN_IDENTIFIER,  $identifier)
            ->addRule($operator,   $literalTokens,           $literal)
            ->addRule($identifier, Lexer::TOKEN_LEFT_PAREN,  $fnOpen)
            ->addRule($fnOpen,     Lexer::TOKEN_IDENTIFIER,  $identifier)
            ->addRule($fnOpen,     $literalTokens,           $literal)
            ->addRule($fnOpen,     Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($parenClose, Lexer::TOKEN_COMMA,       $comma)
            ->addRule($parenClose, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($parenClose, Lexer::TOKEN_OPERATOR,    $operator)
            ->addRule($parenClose, Lexer::TOKEN_STATETERM,   $stateTerm)
            ->addRule($comma,      $literalTokens,           $literal)
            ->addRule($comma,      Lexer::TOKEN_IDENTIFIER,  $identifier)
            ->addRule($comma,      Lexer::TOKEN_LEFT_PAREN,  $parenOpen)
            ->addRule($operator,   Lexer::TOKEN_IDENTIFIER,  $identifier)
            ->addRule($operator,   $literalTokens,           $literal)
            ->addRule($operator,   Lexer::TOKEN_LEFT_PAREN,  $parenOpen)
            ->addRule($parenOpen,  Lexer::TOKEN_LEFT_PAREN,  $parenOpen)
            ->addRule($parenOpen,  Lexer::TOKEN_IDENTIFIER,  $identifier)
            ->addRule($parenOpen,  $literalTokens,           $literal)
            ->addRule($parenOpen,  Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            
            ->whenEntering($identifier, $createNode)
            ->whenEntering($literal, $createNode)
            ->whenEntering($operator, $createNode)
            ->whenEntering($parenClose, $closeNode)
            ->whenEntering($parenOpen, $createNode)
            
            ->start(0)
            ->accept($literal)
            ->accept($parenClose)
        ;
        
        return $builder->build();
    }

    private function createNode()
    {
        // Context is the parent node we want to add to.
        $context = end($this->nodeStack);

        if (!($context instanceof ParentNode)) {
            throw new RuntimeException('Cannot create node as no context is present');
        }

        if ($this->currentToken['type'] === Lexer::TOKEN_STRING) {
            $node = new StringLiteral($this->currentToken['value']);
        } elseif ($this->currentToken['type'] === Lexer::TOKEN_NUMBER) {
            $node = new NumberLiteral($this->currentToken['value']);
        } elseif ($this->currentToken['type'] === Lexer::TOKEN_BOOLEAN) {
            $node = new BooleanLiteral($this->currentToken['value']);
        } elseif ($this->currentToken['type'] === Lexer::TOKEN_IDENTIFIER) {
            $node = new FunctionCall($this->currentToken['value']);
        } elseif ($this->currentToken['type'] === Lexer::TOKEN_OPERATOR) {
            $node = new BinaryOperation($this->currentToken['value'], $context->getLastChild());
        } elseif ($this->currentToken['type'] === Lexer::TOKEN_LEFT_PAREN) {
            $node = new Container();
        } else {
            throw new RuntimeException('Unhandled type "' . $this->currentToken['type'] . '" in Jaslang parser');
        }
        
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
            $this->closeNode();
        }
        
        if ($node instanceof ParentNode) {
            array_push($this->nodeStack, $node);
        }
    }

    private function closeNode()
    {
        if (count($this->nodeStack) <= 1) {
            // We've been asked to close a node that doesn't exist.
            // This means we're closing too many functions, e.g.
            // foo())
            throw new UnexpectedTokenException($this->input, $this->currentToken);
        }

        array_pop($this->nodeStack);
    }
}
