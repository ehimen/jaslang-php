<?php

namespace Ehimen\Jaslang\Engine\Parser;

use Ehimen\Jaslang\Engine\Ast\Node\Block;
use Ehimen\Jaslang\Engine\Ast\Node\Identifier;
use Ehimen\Jaslang\Engine\Ast\Node\Operator;
use Ehimen\Jaslang\Engine\Ast\Node\Container;
use Ehimen\Jaslang\Engine\Ast\Node\FunctionCall;
use Ehimen\Jaslang\Engine\Ast\Node\Literal;
use Ehimen\Jaslang\Engine\Ast\Node\ParentNode;
use Ehimen\Jaslang\Engine\Ast\Node\PrecedenceRespectingNode;
use Ehimen\Jaslang\Engine\Ast\Node\Root;
use Ehimen\Jaslang\Engine\Ast\Node\Statement;
use Ehimen\Jaslang\Engine\Ast\Node\Tuple;
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
        
        // Close any operators that are expecting to be closed
        // as this is the end of input.
        $this->closeOperators();
        
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
        $tupleOpenTokens   = Lexer::TUPLE_OPEN_TOKENS;
        $tupleCloseTokens   = Lexer::TUPLE_CLOSE_TOKENS;

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
        $tupleOpen  = 'tuple-open';
        $tupleClose = 'tuple-close';

        $builder
            ->addRule($start, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($start, $literalTokens, $literal)
            ->addRule($start, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            ->addRule($start, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($start, Lexer::TOKEN_LEFT_BRACE, $blockOpen)
            ->addRule($start, $tupleOpenTokens, $tupleOpen)
            ->addRule($literal, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($literal, Lexer::TOKEN_COMMA, $comma)
            ->addRule($literal, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($literal, Lexer::TOKEN_STATETERM, $stateTerm)
            ->addRule($literal, Lexer::TOKEN_LITERAL, $literal)
            ->addRule($literal, Lexer::TOKEN_RIGHT_BRACE, $blockClose)
            ->addRule($literal, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($literal, Lexer::TOKEN_LEFT_BRACE, $blockOpen)
            ->addRule($literal, $tupleCloseTokens, $tupleClose)
            ->addRule($operator, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($operator, $literalTokens, $literal)
            ->addRule($operator, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($operator, Lexer::TOKEN_STATETERM, $stateTerm)
            ->addRule($operator, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($operator, Lexer::TOKEN_LEFT_BRACE, $blockOpen)
            ->addRule($identifier, Lexer::TOKEN_LEFT_PAREN, $fnOpen)
            ->addRule($identifier, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($identifier, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($identifier, Lexer::TOKEN_COMMA, $comma)
            ->addRule($identifier, Lexer::TOKEN_STATETERM, $stateTerm)
            ->addRule($identifier, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($identifier, Lexer::TOKEN_RIGHT_BRACE, $blockClose)
            ->addRule($identifier, $tupleOpenTokens, $tupleOpen)
            ->addRule($fnOpen, Lexer::TOKEN_IDENTIFIER, $identifier)
            ->addRule($fnOpen, $literalTokens, $literal)
            ->addRule($fnOpen, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($fnOpen, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($fnOpen, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
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
            ->addRule($blockClose, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            ->addRule($tupleOpen, $tupleOpenTokens, $tupleOpen)
            ->addRule($tupleOpen, Lexer::TOKEN_LEFT_PAREN, $parenOpen)
            ->addRule($tupleOpen, $literalTokens, $literal)
            ->addRule($tupleOpen, $tupleCloseTokens, $tupleClose)
            ->addRule($tupleClose, Lexer::TOKEN_STATETERM, $stateTerm)
            ->addRule($tupleClose, $tupleCloseTokens, $tupleClose)
            ->addRule($tupleClose, Lexer::TOKEN_OPERATOR, $operator)
            ->addRule($tupleClose, Lexer::TOKEN_RIGHT_PAREN, $parenClose)
            
            
            ->whenEntering($identifier, $createNode)
            ->whenEntering($literal, $createNode)
            ->whenEntering($operator, $createNode)
            ->whenEntering($parenClose, $closeNode)
            ->whenEntering($parenOpen, $createNode)
            ->whenEntering($stateTerm, $closeNode)
            ->whenEntering($blockOpen, $createNode)
            ->whenEntering($blockClose, $closeNode)
            ->whenEntering($tupleOpen, $createNode)
            ->whenEntering($tupleClose, $closeNode)
            ->whenEntering($comma, $closeNode)
            
            ->start($start)
            ->accept($literal)
            ->accept($parenClose)
            ->accept($operator)
            ->accept($identifier)
            ->accept($blockClose)
            ->accept($stateTerm)
            ->accept($tupleClose)
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
            $node = $this->adjustForPrecedence($context, new Operator(
                $this->currentToken->getValue(),
                $this->functionRepository->getOperatorSignature($this->currentToken->getValue())
            ));
        } elseif ($this->currentToken->getType() === Lexer::TOKEN_LEFT_BRACE) {
            $node = new Block();
        } elseif ($this->currentToken->getType() === Lexer::TOKEN_LEFT_PAREN) {
            $node = new Container();
        } elseif (in_array($this->currentToken->getType(), Lexer::TUPLE_OPEN_TOKENS, true)) {
            $node = $this->adjustForPrecedence(
                $context,
                new Tuple($this->functionRepository->getListOperatorSignature($this->currentToken->getValue()))
            );
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
        
        // TODO: Need to handle broken case, e.g. "print(subtract(13 + 24, 7 + 5))"
        // We've removed the closing of node on creation and moved it to statement/block
        // closing, but maybe we need to add this to when we encounter the next non-precedence
        // respecting node; when we encounter that we can close operator that are hanging
        // around. Note: we moved the closing from node creation because in array init,
        // we had a problem where "nums : number[]" would mean ":" is closed off iteratively after
        // "number" being encountered, so our "[]" could not override precendence and consume "number".

        if (!($node instanceof ParentNode) && !($this->lastNode instanceof ParentNode)) {
            $this->closeOperators(false);
            $context = end($this->nodeStack);
        }

        if ($context !== $node) {
            // It may be that precedence readjustment means that our context has shifted
            // to the node we've just created. Only add the child node if this is not
            // the case.
            $context->addChild($node);
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

        $this->lastNode = $node;
    }

    private $lastNode;

    private function isHeadClosableOperator($failIfCantBeClosed = true)
    {
        $head = end($this->nodeStack);
        
        if ($head instanceof Operator) {
            if ($head->canBeClosed()) {
                return true;
            } elseif ($failIfCantBeClosed) {
                throw new UnexpectedTokenException($this->input, $this->currentToken);
            }
        }

        return false;
    }

    private function closeOperators($failIfCantBeClosed = true)
    {
        $closed = 0;
        
        while ($this->isHeadClosableOperator($failIfCantBeClosed)) {
            array_pop($this->nodeStack);
            $closed++;
        }
        
        return $closed;
    }

    private function closeStatements()
    {
        while ($this->isHeadClosableOperator() || end($this->nodeStack) instanceof Statement) {
            array_pop($this->nodeStack);
        }
    }

    private function closeNode()
    {
        $type = $this->currentToken->getType();

        if ($type === Lexer::TOKEN_STATETERM) {
            $this->closeStatements();
        } elseif ($type === Lexer::TOKEN_RIGHT_BRACE) {
            // State termination is optional in final statement of a block.
            $this->closeStatements();
            
            // Now close off our blocks.
            if (end($this->nodeStack) instanceof Block) {
                array_pop($this->nodeStack);
            }
            
            // Blocks don't need to be terminated explicitly, so close all statements.
            $this->closeStatements();
        } elseif (in_array($type, Lexer::TUPLE_CLOSE_TOKENS, true)) {
            if (!(end($this->nodeStack) instanceof Tuple)) {
                throw new UnexpectedTokenException($this->input, $this->currentToken);
            }

            array_pop($this->nodeStack);
        } elseif ($this->currentToken->getType() === Lexer::TOKEN_COMMA) {
            // A comma implies any operators prior to it are complete; close them.
            $this->closeOperators();
        } elseif ($this->currentToken->getType() === Lexer::TOKEN_RIGHT_PAREN) {
            $this->closeOperators();
            
            $head = end($this->nodeStack);
            
            if (!($head instanceof Container) && !($head instanceof FunctionCall)) {
                throw new UnexpectedTokenException($this->input, $this->currentToken);
            }
            
            array_pop($this->nodeStack);
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
     * @return PrecedenceRespectingNode
     */
    private function adjustForPrecedence(ParentNode &$context, PrecedenceRespectingNode $current)
    {
        $signature = $current->getSignature();

        if ($signature->hasLeftArg() && ($context instanceof PrecedenceRespectingNode)) {

            while (true) {
                $parentOfContext = isset($this->nodeStack[count($this->nodeStack) - 2])
                    ? $this->nodeStack[count($this->nodeStack) - 2]
                    : null;

                if ($parentOfContext instanceof PrecedenceRespectingNode && !$signature->takesPrecedenceOver($parentOfContext->getSignature())) {
                    array_pop($this->nodeStack);
                    $context = end($this->nodeStack);
                } else {
                    break;
                }
            }

            if ($context instanceof PrecedenceRespectingNode) {
                if ($signature->takesPrecedenceOver($context->getSignature())) {
                    // This token has greater precedence, which means it should
                    // appear lower down in the AST. Simply take the last
                    // child from the context and add it as a child of this
                    // node. This node will be added to context later.
                    $current->addChild($context->getLastChild());
                    $context->removeLastChild();
                } else {
                    // This token has less precedence, which means it should
                    // appear higher up the AST. Replace context with this
                    // node and add the old context as a child of this node.
                    array_pop($this->nodeStack);
                    $current->addChild($context);
                    $context = end($this->nodeStack);
                    $context->removeLastChild();
                }
            }
        } elseif (!($context instanceof PrecedenceRespectingNode)) {
            // TODO: wtf is this case?
            if ($signature->hasLeftArg()) {
                $child = $context->getLastChild();
                $context->removeLastChild();
                $current->addChild($child);
            }
        }

        return $current;

        for ($i = 0; $i < $signature->hasLeftArg(); $i++) {
            $lastChild = $context->getLastChild();

            if ($lastChild instanceof PrecedenceRespectingNode) {
                $previousSignature = $lastChild->getSignature();

                // TODO: We have two cases here.
                // 1: 3 - 1 + 2
                // "3 - 1", + wants to overwrite the - as parent, to result in (3 - 1) + 1 to ensure 3 - 1 is evaluated first.
                // 2: nums : number[]
                // [] wants to overwrite number as the second child of :, to result in nums : (number[])
                // Could we interrogate expected parameter count of current node an only overwrite parent
                // if we expected >1 parameters (covers + case), else only consume last child if expected parameter
                // count === 1 (covers [] case).
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
            $current->addChild($child);
        }
        
        return $current;
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
