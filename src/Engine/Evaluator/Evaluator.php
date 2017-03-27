<?php

namespace Ehimen\Jaslang\Engine\Evaluator;

use Ehimen\Jaslang\Engine\Ast\Node;
use Ehimen\Jaslang\Engine\Ast\Visitor;
use Ehimen\Jaslang\Engine\Evaluator\Context\ContextStack;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedFunctionException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedOperatorException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedSymbolException;
use Ehimen\Jaslang\Engine\Evaluator\Trace\EvaluationTrace;
use Ehimen\Jaslang\Engine\Evaluator\Trace\TraceEntry;
use Ehimen\Jaslang\Engine\Exception\LogicException;
use Ehimen\Jaslang\Engine\Exception\NotSupportedException;
use Ehimen\Jaslang\Engine\Exception\OutOfBoundsException;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Argument;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Collection;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expression;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Routine;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeIdentifier;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Variable;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\FuncDef\FunctionRepository;
use Ehimen\Jaslang\Engine\Value\CallableValue;

class Evaluator implements Visitor
{
    private $argumentStack = [];

    /**
     * @var EvaluationTrace
     */
    private $trace;

    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var FunctionRepository
     */
    private $functionRepository;

    /**
     * @var ContextStack
     */
    private $contextStack;

    public function __construct(Invoker $invoker, FunctionRepository $functionRepository, ContextStack $contextFactory)
    {
        $this->invoker            = $invoker;
        $this->functionRepository = $functionRepository;
        $this->contextStack     = $contextFactory;
    }

    public function reset()
    {
        $this->trace         = new EvaluationTrace();
        $this->argumentStack = [];
        $this->contextStack->reset();
    }

    public function pushContext()
    {
        $this->contextStack->createContext();
    }

    public function popContext()
    {
        $this->contextStack->popContext();
    }

    /**
     * @return EvaluationTrace
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * @return EvaluationContext
     */
    public function getContext()
    {
        return $this->contextStack->getContext();
    }

    /**
     * Creates a new stack frame, evaluates the node, clears the stack frame and returns any result
     * from the evaluation.
     * 
     * Note this isn't strictly isolation (needs a better name) because:
     * 1. Execution context is the same (symbol table, functions etc)
     * 2. We just push on to the argument stack to make sure nothing that the 
     *    evaluator is currently evaluating is effected by this evaluation.
     * 
     * This is useful when userland contexts (e.g. Jaslang Core) need to invoke the
     * evaluator, such as evaluating the parameters of a lambda.
     * 
     * @return Argument
     */
    public function evaluateInIsolation(Node\Node $node)
    {
        $this->pushArgument();
        $node->accept($this);
        return $this->getResult();
    }

    /**
     * @return Argument
     */
    public function getResult()
    {
        if (!isset($this->argumentStack[0])) {
            throw new LogicException('Evaluator does not have a result.');
        }
        
        return current($this->popArgument());
    }
    
    public function visitBlock(Node\Block $node)
    {
        $this->evaluateChildrenOf($node);
    }

    public function visitContainer(Node\Container $node)
    {
        $this->evaluateChildrenOf($node);
    }

    public function visitTuple(Node\Tuple $node)
    {
        $this->pushTrace($node);
        
        try {
            $operation = $this->functionRepository->getListOperation($node->getSignature()->getEnclosureStart());
        } catch (OutOfBoundsException $e) {
            throw new RuntimeException(sprintf(
                'Unknown enclosing syntax: %s %s',
                $node->getEnclosureStart(),
                $node->getEnclosureEnd()
            ));
        }
        
        $this->pushArgument();
        
        $this->visitChildrenOf($node);
        
        $arguments = $this->popArgument();
        
        $result = $this->invoker->invokeFunction(
            $operation,
            new ArgList($arguments),
            $this
        );
        
        $this->pushArgument($result);
        
        $this->popTrace();
    }

    public function visitFunctionCall(Node\FunctionCall $node)
    {
        $this->pushTrace($node);
        
        try {
            $callable = $this->functionRepository->getFunction($node->getName());
        } catch (OutOfBoundsException $e) { }
        
        // If we don't have a native function, see if we have
        // a callable with this function name in our symbol table.
        if ($this->getContext()->getSymbolTable()->hasCallable($node->getName())) {
            $callable = $this->getContext()->getSymbolTable()->getCallable($node->getName());
        }
        
        if (!isset($callable)) {
            throw new UndefinedFunctionException($node->getName());
        }
        
        $this->pushArgument();

        $this->visitChildrenOf($node);

        $arguments = $this->popArgument();

        if ($callable instanceof FuncDef) {
            $result = $this->invoker->invokeFunction(
                $callable,
                new ArgList($arguments),
                $this
            );
        } elseif ($callable instanceof CallableValue) {
            $result = $this->invoker->invokeCallable(
                $callable,
                new ArgList($arguments),
                $this
            );
        } else {
            throw new RuntimeException('Unknown callable');
        }
        
        $this->pushArgument($result);
        
        $this->popTrace();
    }

    public function visitIdentifier(Node\Identifier $node)
    {
        try {
            $this->pushArgument($this->getContext()->getSymbolTable()->get($node->getName()));
        } catch (OutOfBoundsException $e) {
            throw new UndefinedSymbolException($node->getName());
        }
    }

    public function visitLiteral(Node\Literal $node)
    {
        $this->pushArgument($node->getType()->createValue($node->getValue()));
    }

    public function visitOperator(Node\Operator $node)
    {
        $this->pushTrace($node);
        
        try {
            $operator = $this->functionRepository->getOperator($node->getOperator());
        } catch (OutOfBoundsException $e) {
            throw new UndefinedOperatorException($node->getOperator());
        }

        $this->pushArgument();

        $this->visitChildrenOf($node);

        $arguments = $this->popArgument();

        $this->pushArgument($this->invoker->invokeFunction(
            $operator,
            new ArgList($arguments),
            $this
        ));
        
        $this->popTrace();
    }

    public function visitRoot(Node\Root $node)
    {
        $this->evaluateChildrenOf($node);
    }

    public function visitStatement(Node\Statement $node)
    {
        $this->popArgument();
        $this->evaluateChildrenOf($node);
    }

    private function pushTrace(Node\Node $node)
    {
        $this->trace->push(new TraceEntry($node->debug()));
    }

    private function popTrace()
    {
        $this->trace->pop();
    }

    private function evaluateChildrenOf(Node\ParentNode $node)
    {
        foreach ($node->getChildren() as $child) {
            $child->accept($this);
        }
    }

    /**
     * @param Node\ParentNode $node
     */
    private function visitChildrenOf(Node\ParentNode $node)
    {
        foreach ($node->getChildren() as $child) {
            if ($child instanceof Node\Identifier) {
                $identifier = $child->getName();
                
                $argument   = $this->getContext()->getSymbolTable()->hasType($identifier)
                    ? new TypeIdentifier($identifier)
                    : new Variable($identifier);
                
                $this->pushArgument($argument);
            } elseif ($child instanceof Node\Routine) {
                $this->pushArgument(new Routine($child));
            } elseif ($child instanceof Node\Expression) {
                $this->pushArgument(new Expression($child));
            } elseif ($child instanceof Node\Container) {
                $collection = new Collection($child);
                
                foreach ($child->getChildren() as $containerChild) {
                    if (!($containerChild instanceof Node\Expression)) {
                        throw new NotSupportedException('Parameter collection beyond expression are not supported');
                    }
                    
                    $collection->addArgument(new Expression($containerChild));
                }

                $this->pushArgument($collection);
            } else {
                throw new NotSupportedException(sprintf('Cannot push child "%s" to argument stack', $child->debug()));
            }
        }
    }

    private function pushArgument(Argument $argument = null)
    {
        if ($argument) {
            $this->argumentStack[0][] = $argument;
        } else {
            array_unshift($this->argumentStack, []);
        }
    }

    /**
     * @return Argument[]
     */
    private function popArgument()
    {
        return array_shift($this->argumentStack);
    }
}
