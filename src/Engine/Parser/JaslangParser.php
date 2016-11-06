<?php

namespace Ehimen\Jaslang\Engine\Parser;

use Ehimen\Jaslang\Engine\Ast\Operator;
use Ehimen\Jaslang\Engine\Ast\Container;
use Ehimen\Jaslang\Engine\Ast\FunctionCall;
use Ehimen\Jaslang\Engine\Ast\Literal;
use Ehimen\Jaslang\Engine\Ast\ParentNode;
use Ehimen\Jaslang\Engine\Ast\Root;
use Ehimen\Jaslang\Engine\FuncDef\FunctionRepository;
use Ehimen\Jaslang\Engine\Type\TypeRepository;
use Ehimen\Jaslang\Engine\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\Lexer\Lexer;
use Ehimen\Jaslang\Engine\Lexer\Token;
use Ehimen\Jaslang\Engine\Parser\Dfa\DfaBuilder;
use Ehimen\Jaslang\Engine\Parser\Dfa\Exception\NotAcceptedException;
use Ehimen\Jaslang\Engine\Parser\Dfa\Exception\TransitionImpossibleException;
use Ehimen\Jaslang\Engine\Parser\Exception\UnexpectedEndOfInputException;
use Ehimen\Jaslang\Engine\Parser\Exception\UnexpectedTokenException;

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
        
        $closeNode = function () {
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
            ->addRule($start, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($start, $literalTokens, $literal)
            ->addRule($start, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            ->addRule($start, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($literal, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($literal, Lexer::TOKEN_COMMA, $comma)
            ->addRule($literal, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($literal, Lexer::TOKEN_STATETERM, $stateTerm)
            ->addRule($literal, Lexer::TOKEN_LITERAL, $literal)
            ->addRule($operator, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($operator, $literalTokens, $literal)
            ->addRule($identifier, Lexer::TOKEN_LEFT_PAREN, $fnOpen)
            ->addRule($fnOpen, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($fnOpen, $literalTokens, $literal)
            ->addRule($fnOpen, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($parenClose, Lexer::TOKEN_COMMA, $comma)
            ->addRule($parenClose, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($parenClose, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($parenClose, Lexer::TOKEN_STATETERM, $stateTerm)
            ->addRule($comma, $literalTokens, $literal)
            ->addRule($comma, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($comma, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            ->addRule($operator, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($operator, $literalTokens, $literal)
            ->addRule($operator, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            ->addRule($parenOpen, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            ->addRule($parenOpen, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($parenOpen, $literalTokens, $literal)
            ->addRule($parenOpen, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($stateTerm, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($stateTerm, $literalTokens, $literal)
            ->addRule($stateTerm, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            
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
            $node = $this->createLiteral();
        } elseif ($this->currentToken->getType() === Lexer::TOKEN_IDENTIFIER) {
            $node = new FunctionCall($this->currentToken->getValue());
        } elseif ($this->currentToken->getType() === Lexer::TOKEN_OPERATOR) {
            $node = $this->createOperator($context);
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
        
        $context->addChild($node);
        
        // Creation of nodes can imply a closing of operator nodes.
        // E.g. for the term "3 + 4", we'd close the operator on the
        // final token, "4".
        // This loop closes all operators that we can in case termination
        // of this operator implies termination of other.e.g. "3 + 4 + 5".
        // The "5" terminates both operator nodes.
        while (($context instanceof Operator) && $context->canBeClosed()) {
            $this->closeNode();
            $context = end($this->nodeStack);
        }
        
        if ($node instanceof ParentNode) {
            // If we have a node that can have children,
            // push this to our stack so that the next iteration
            // will be creating nodes in this context.
            array_push($this->nodeStack, $node);

            // Operator nodes are a special case as we may need to
            // close them immediately if they've already been
            // constructed with all of their required operands.
            // Example, a postfix operator that has already has
            // its operand as it was the previous token. We've shuffled
            // the AST when creating the node, we just need to close
            // the the node now, looping in case the closing of this node
            // means that parent nodes are closable.
            while (($node instanceof Operator) && $node->canBeClosed()) {
                $this->closeNode();
                $node = end($this->nodeStack);
            }
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

    /**
     * This logic handles operator precedence.
     *
     * Because some operators can appear after their operands (postfix),
     * we sometimes need reshuffle the AST a bit. Context made be modified
     * to reflect a potentially new context node.
     *
     * @param ParentNode $context
     *
     * @return Operator
     */
    private function createOperator(ParentNode &$context)
    {
        $signature = $this->functionRepository->getOperatorSignature($this->currentToken->getValue());
        $node      = new Operator($this->currentToken->getValue(), $signature);
        $children  = [];

        for ($i = 0; $i < $signature->getLeftArgs(); $i++) {
            $lastChild = $context->getLastChild();

            if ($lastChild instanceof Operator) {
                $previousSignature = $this->functionRepository->getOperatorSignature($lastChild->getOperator());

                if ($signature->takesPrecedenceOver($previousSignature)) {
                    array_unshift($children, $lastChild->getLastChild());
                    $lastChild->removeLastChild();
                    $context = $lastChild;
                    array_push($this->nodeStack, $context);
                    continue;
                }
            }

            $context->removeLastChild();
            array_unshift($children, $lastChild);
        }

        foreach ($children as $child) {
            $node->addChild($child);
        }
        
        return $node;
    }

    /**
     * @return Literal|null
     */
    private function createLiteral()
    {
        foreach ($this->typeRepository->getConcreteTypes() as $type) {
            if ($type->appliesToToken($this->currentToken)) {
                return new Literal($type, $this->currentToken->getValue());
            }
        }
        
        return null;
    }
}
