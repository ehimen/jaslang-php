<?php

namespace Ehimen\Jaslang\Parser;

use Ehimen\Jaslang\Ast\Operator;
use Ehimen\Jaslang\Ast\BooleanLiteral;
use Ehimen\Jaslang\Ast\Container;
use Ehimen\Jaslang\Ast\FunctionCall;
use Ehimen\Jaslang\Ast\Literal;
use Ehimen\Jaslang\Ast\NumberLiteral;
use Ehimen\Jaslang\Ast\ParentNode;
use Ehimen\Jaslang\Ast\Root;
use Ehimen\Jaslang\Ast\StringLiteral;
use Ehimen\Jaslang\FuncDef\FunctionRepository;
use Ehimen\Jaslang\Type\TypeRepository;
use Ehimen\Jaslang\Exception\RuntimeException;
use Ehimen\Jaslang\Lexer\JaslangLexer;
use Ehimen\Jaslang\Lexer\Lexer;
use Ehimen\Jaslang\Lexer\Token;
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

    /**
     * @var Token
     */
    private $currentToken;

    /**
     * @var Root
     */
    private $ast;

    /**
     * @var string
     */
    private $input;

    /**
     * @var FunctionRepository
     */
    private $functionRepository;

    /**
     * @var TypeRepository
     */
    private $typeRepository;

    public function __construct(Lexer $lexer, FunctionRepository $fnRepo, TypeRepository $typeRepo)
    {
        $this->lexer              = $lexer;
        $this->functionRepository = $fnRepo;
        $this->typeRepository     = $typeRepo;
    }

    public function parse($input)
    {
        $this->input = $input;
        $dfa = $this->getDfa();

        $tokens = array_values(array_filter(
            $this->lexer->tokenize($input),
            function (Token $token) {
                return ($token->getType() !== Lexer::TOKEN_WHITESPACE);
            }
        ));

        $this->ast = new Root();
        $this->nodeStack = [$this->ast];
        
        foreach ($tokens as $i => $token) {
            $this->currentToken = $token;
            
            try {
                $dfa->transition($token->getType());
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

        $literalTokens = Lexer::LITERAL_TOKENS;

        $start      = 0;
        $literal    = 'literal';
        $operator   = 'operator';
        $identifier = 'identifier';
        $fnOpen     = 'fn-open';
        $parenOpen  = 'paren-open';
        $parenClose = 'paren-close';
        $comma      = 'comma';
        $stateTerm  = 'state-term';

        $builder
            ->addRule($start,      Lexer::TOKEN_IDENTIFIER,  $identifier)
            ->addRule($start,      $literalTokens,           $literal)
            ->addRule($start,      Lexer::TOKEN_LEFT_PAREN,  $parenOpen)
            ->addRule($start,      Lexer::TOKEN_OPERATOR,    $operator)
            ->addRule($literal,    Lexer::TOKEN_OPERATOR,    $operator)
            ->addRule($literal,    Lexer::TOKEN_COMMA,       $comma)
            ->addRule($literal,    Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($literal,    Lexer::TOKEN_STATETERM,   $stateTerm)
            ->addRule($literal,    Lexer::TOKEN_LITERAL,     $literal)
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
            ->addRule($stateTerm,  Lexer::TOKEN_IDENTIFIER,  $identifier)
            ->addRule($stateTerm,  $literalTokens,           $literal)
            ->addRule($stateTerm,  Lexer::TOKEN_LEFT_PAREN,  $parenOpen)
            
            ->whenEntering($identifier, $createNode)
            ->whenEntering($literal, $createNode)
            ->whenEntering($operator, $createNode)
            ->whenEntering($parenClose, $closeNode)
            ->whenEntering($parenOpen, $createNode)
            
            ->start($start)
            ->accept($literal)
            ->accept($parenClose)
            ->accept($operator)
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
        
        if ($this->currentToken->isLiteral()) {
            foreach ($this->typeRepository->getConcreteTypes() as $type) {
                if ($type->appliesToToken($this->currentToken)) {
                    $node = new Literal($type, $this->currentToken->getValue());
                    break;
                }
            }
        } elseif ($this->currentToken->getType() === Lexer::TOKEN_IDENTIFIER) {
            $node = new FunctionCall($this->currentToken->getValue());
        } elseif ($this->currentToken->getType() === Lexer::TOKEN_OPERATOR) {
            $thisSignature = $this->functionRepository->getOperatorSignature($this->currentToken->getValue());
            $node          = new Operator($this->currentToken->getValue(), $thisSignature);
            $children      = [];

            for ($i = 0; $i < $thisSignature->getLeftArgs(); $i++) {
                $lastChild = $context->getLastChild();

                if ($lastChild instanceof Operator) {
                    $previousSignature = $this->functionRepository->getOperatorSignature($lastChild->getOperator());

                    if ($thisSignature->getPrecedence() > $previousSignature->getPrecedence()) {
                        array_unshift($children, $lastChild->getLastChild());
                        $lastChild->removeLastChild();
                        $context = $lastChild;
                        continue;
                    }
                }

                $context->removeLastChild();
                array_unshift($children, $lastChild);
            }

            foreach ($children as $child) {
                $node->addChild($child);
            }
        } elseif ($this->currentToken->getType() === Lexer::TOKEN_LEFT_PAREN) {
            $node = new Container();
        }
        
        if (!isset($node)) {
            throw new RuntimeException(sprintf(
                'Unhandled token "%s" [type: %s] in Jaslang parser',
                $this->currentToken->getValue(),
                $this->currentToken->getType()
            ));
        }

        // If we're creating a binary node, its infix nature
        // means we need to shift the AST a bit.
        // We take the place of the most recently added
        // node in our context. We ensure our operator takes
        // what we're replacing as its LHS (done on construction
        // above). If it's not a binary operation, we simply add
        // it to the end of the parent's children.
        $context->addChild($node);
        // TODO: don't want to do the above if $context is an operator that doesn't accept RHS args.

        if (($context instanceof Operator) && $context->canBeClosed()) {
            // All arguments of an operator have been added. Close it.
            $this->closeNode();
        }
        
        if ($node instanceof ParentNode) {
            array_push($this->nodeStack, $node);
        }

        if (($node instanceof Operator) && $node->canBeClosed()) {
            // All arguments of an operator have been added. Close it.
            $this->closeNode();
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
