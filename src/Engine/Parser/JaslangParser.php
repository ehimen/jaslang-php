<?php

namespace Ehimen\Jaslang\Engine\Parser;

use Ehimen\Jaslang\Engine\Ast\Node\Block;
use Ehimen\Jaslang\Engine\Ast\Node\Identifier;
use Ehimen\Jaslang\Engine\Ast\Node\Operator;
use Ehimen\Jaslang\Engine\Ast\Node\Container;
use Ehimen\Jaslang\Engine\Ast\Node\FunctionCall;
use Ehimen\Jaslang\Engine\Ast\Node\Literal;
use Ehimen\Jaslang\Engine\Ast\Node\ParentNode;
use Ehimen\Jaslang\Engine\Ast\Node\Root;
use Ehimen\Jaslang\Engine\Ast\Node\Statement;
use Ehimen\Jaslang\Engine\Exception\LogicException;
use Ehimen\Jaslang\Engine\FuncDef\FunctionRepository;
use Ehimen\Jaslang\Engine\Parser\Validator\Validator;
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
     * @var Token
     */
    private $nextToken;

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

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var int[]
     * 
     * Tracks how many statements should be open at any one time.
     */
    private $statementStack = [];

    /**
     * @var int
     * 
     * Tracks how many blocks are open at any one time. Used to ensure that
     * all block nodes' immediate children are statements.
     */
    private $blockDepth = 1;

    /**
     * @var NodeCreationObserver[]
     */
    private $nodeCreationObservers = [];

    public function __construct(Lexer $lexer, FunctionRepository $fnRepo, TypeRepository $typeRepo, Validator $validator)
    {
        $this->lexer              = $lexer;
        $this->functionRepository = $fnRepo;
        $this->typeRepository     = $typeRepo;
        $this->validator          = $validator;
    }

    public function registerNodeCreationObserver(NodeCreationObserver $observer)
    {
        $this->nodeCreationObservers[] = $observer;
    }

    public function parse($input)
    {
        $this->input = $input;
        $dfa = $this->getDfa();

        /** @var Token[] $tokens */
        $tokens = array_values(array_filter(
            $this->lexer->tokenize($input),
            function (Token $token) {
                return ($token->getType() !== Lexer::TOKEN_WHITESPACE);
            }
        ));

        $this->ast = new Root();
        $this->nodeStack = [$this->ast];
        $this->statementStack = [0];
        
        foreach ($tokens as $i => $token) {
            $this->currentToken = $token;
            $this->nextToken    = isset($tokens[$i + 1]) ? $tokens[$i + 1] : null;
            
            try {
                $dfa->transition($token->getType());
            } catch (TransitionImpossibleException $e) {
                throw new UnexpectedTokenException($input, $token);
            }
        }
        
        $finalStatement = end($this->nodeStack);
        
        if ($finalStatement instanceof Statement) {
            // If our stack still has a statement on it, this
            // is an unterminated statement. This is fine,
            // remove it before we conclude our checks. 
            array_pop($this->nodeStack);
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
        
        $this->validator->validate($input, $this->ast);
        
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
        $blockOpen  = 'block-open';
        $blockClose = 'block-close';

        $builder
            ->addRule($start, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($start, $literalTokens, $literal)
            ->addRule($start, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            ->addRule($start, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($start, Lexer::TOKEN_LEFT_BRACE, $blockOpen)
            ->addRule($literal, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($literal, Lexer::TOKEN_COMMA, $comma)
            ->addRule($literal, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($literal, Lexer::TOKEN_STATETERM, $stateTerm)
            ->addRule($literal, Lexer::TOKEN_LITERAL, $literal)
            ->addRule($literal, Lexer::TOKEN_RIGHT_BRACE, $blockClose)
            ->addRule($literal, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($literal, Lexer::TOKEN_LEFT_BRACE, $blockOpen)
            ->addRule($operator, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($operator, $literalTokens, $literal)
            ->addRule($operator, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($operator, Lexer::TOKEN_STATETERM, $stateTerm)
            ->addRule($identifier, Lexer::TOKEN_LEFT_PAREN, $fnOpen)
            ->addRule($identifier, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($identifier, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($identifier, Lexer::TOKEN_COMMA, $comma)
            ->addRule($identifier, Lexer::TOKEN_STATETERM, $stateTerm)
            ->addRule($identifier, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($identifier, Lexer::TOKEN_RIGHT_BRACE, $blockClose)
            ->addRule($fnOpen, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($fnOpen, $literalTokens, $literal)
            ->addRule($fnOpen, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($parenClose, Lexer::TOKEN_COMMA, $comma)
            ->addRule($parenClose, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($parenClose, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($parenClose, Lexer::TOKEN_STATETERM, $stateTerm)
            ->addRule($parenClose, Lexer::TOKEN_RIGHT_BRACE, $blockClose)
            ->addRule($parenClose, Lexer::TOKEN_LEFT_BRACE, $blockOpen)
            ->addRule($parenClose, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($comma, $literalTokens, $literal)
            ->addRule($comma, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($comma, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            ->addRule($operator, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($operator, $literalTokens, $literal)
            ->addRule($operator, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            ->addRule($operator, Lexer::TOKEN_RIGHT_BRACE, $blockClose)
            ->addRule($parenOpen, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            ->addRule($parenOpen, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($parenOpen, $literalTokens, $literal)
            ->addRule($parenOpen, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($parenOpen, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($stateTerm, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($stateTerm, $literalTokens, $literal)
            ->addRule($stateTerm, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            ->addRule($stateTerm, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($stateTerm, Lexer::TOKEN_RIGHT_BRACE, $blockClose)
            ->addRule($blockOpen, Lexer::TOKEN_RIGHT_BRACE, $blockClose)
            ->addRule($blockOpen, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($blockOpen, $literalTokens, $literal)
            ->addRule($blockOpen, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            ->addRule($blockOpen, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($blockOpen, Lexer::TOKEN_LEFT_BRACE, $blockOpen)
            ->addRule($blockClose, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($blockClose, Lexer::TOKEN_LEFT_BRACE, $blockOpen)
            ->addRule($blockClose, Lexer::TOKEN_RIGHT_BRACE, $blockClose)
            ->addRule($blockClose, Lexer::TOKEN_OPERATOR, $operator)
            
            ->whenEntering($identifier, $createNode)
            ->whenEntering($literal, $createNode)
            ->whenEntering($operator, $createNode)
            ->whenEntering($parenClose, $closeNode)
            ->whenEntering($parenOpen, $createNode)
            ->whenEntering($stateTerm, $closeNode)
            ->whenEntering($blockOpen, $createNode)
            ->whenEntering($blockClose, $closeNode)
            
            ->start($start)
            ->accept($literal)
            ->accept($parenClose)
            ->accept($operator)
            ->accept($identifier)
            ->accept($blockClose)
        ;
        
        return $builder->build();
    }

    private function createNode()
    {
        // Context is the parent node we want to add to.
        $context = end($this->nodeStack);

        if (!($context instanceof ParentNode)) {
            throw new LogicException('Cannot create node as no context is present');
        }
        
        $insertStatement = true;
        
        foreach (array_reverse($this->nodeStack) as $nodeFromStack) {
            if (($nodeFromStack instanceof Block) || ($nodeFromStack instanceof Root)) {
                break;
            }
            
            if ($nodeFromStack instanceof Statement) {
                $insertStatement = false;
            }
        }
        
        if ($insertStatement) { 
            // This means we haven't created a statement for our current
            // block (including root) so create one now.
            // Bring the statement depth up to our current block depth.
            // This is unwound to this depth when the block is closed.
            // TODO: Cleaner solution to this? Stack approach, or revisit
            // TODO: root/block/statement concepts?
            $statement = new Statement();
            array_push($this->nodeStack, $statement);
            $context->addChild($statement);
            $context = $statement;
            
            // Swap the stack frame to indicate that we've opened a statement.
            array_pop($this->statementStack);
            array_push($this->statementStack, 1);
        }
        
        if ($this->currentToken->isLiteral()) {
            $node = $this->createLiteral();
        } elseif ($this->currentToken->getType() === Lexer::TOKEN_IDENTIFIER) {
            if ($this->isNextToken(Lexer::TOKEN_LEFT_PAREN)) {
                $node = new FunctionCall($this->currentToken->getValue());
            } else {
                $node = new Identifier($this->currentToken->getValue());
            }
        } elseif ($this->currentToken->getType() === Lexer::TOKEN_OPERATOR) {
            $node = $this->createOperator($context);
        } elseif ($this->currentToken->getType() === Lexer::TOKEN_LEFT_BRACE) {
            $node = new Block();
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
        
        foreach ($this->nodeCreationObservers as $observer) {
            $observer->onNodeCreated($node, $this->currentToken);
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
        $type = $this->currentToken->getType();
        
        if ($type === Lexer::TOKEN_STATETERM) {
            while (end($this->nodeStack) instanceof Statement) {
                array_pop($this->nodeStack);
            }
        } elseif ($type === Lexer::TOKEN_RIGHT_BRACE) {
            // State termination is optional in final statement of a block.
            if (end($this->nodeStack) instanceof Statement) {
                array_pop($this->nodeStack);
            }
            // Now close off our blocks.
            if (end($this->nodeStack) instanceof Block) {
                array_pop($this->nodeStack);
            }
            // Blocks don't need to be terminated explicitly, so close all statements.
            while (end($this->nodeStack) instanceof Statement) {
                array_pop($this->nodeStack);
            }
        } else {
            // How many nodes on the stack do we expect for valid
            // closing of a node. 2+ because root, the node we're closing
            // and statement(s) in between.
            // TODO: what about this check in nested statements/blocks.
            $expectedOpen = 2;

            if (count($this->nodeStack) <= $expectedOpen) {
                // We've been asked to close a node that doesn't exist.
                // This means we're closing too many functions, e.g.
                // foo())
                throw new UnexpectedTokenException($this->input, $this->currentToken);
            }

            array_pop($this->nodeStack);
        }
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

    private function isNextToken($type)
    {
        if (null === $this->nextToken) {
            return false;
        }
        
        return ($type === $this->nextToken->getType());
    }
}
