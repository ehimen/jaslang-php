<?php

namespace Ehimen\Jaslang\Engine\Evaluator;

use Ehimen\Jaslang\Engine\Ast\Node;
use Ehimen\Jaslang\Engine\Ast\Visitor;
use Ehimen\Jaslang\Engine\Evaluator\Context\ContextFactory;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedFunctionException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedOperatorException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedSymbolException;
use Ehimen\Jaslang\Engine\Evaluator\Trace\EvaluationTrace;
use Ehimen\Jaslang\Engine\Evaluator\Trace\TraceEntry;
use Ehimen\Jaslang\Engine\Exception\LogicException;
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
     * @var EvaluationContext
     */
    private $context;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    public function __construct(Invoker $invoker, FunctionRepository $functionRepository, ContextFactory $contextFactory)
    {
        $this->invoker            = $invoker;
        $this->functionRepository = $functionRepository;
        $this->contextFactory     = $contextFactory;
    }

    public function reset()
    {
        $this->trace         = new EvaluationTrace();
        $this->argumentStack = [];
        $this->context       = $this->contextFactory->createContext();
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
        return $this->context;
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
        $this->visitChildrenOf($node);
    }

    public function visitContainer(Node\Container $node)
    {
        $this->visitChildrenOf($node);
    }

    public function visitFunctionCall(Node\FunctionCall $node)
    {
        $this->pushTrace($node);
        
        try {
            $callable = $this->functionRepository->getFunction($node->getName());
            $parameters = $callable->getParameters();
        } catch (OutOfBoundsException $e) { }
        
        // If we don't have a native function, see if we have
        // a callable with this function name in our symbol table.
        try {
            $value = $this->context->getSymbolTable()->get($node->getName());
            
            if ($value instanceof CallableValue) {
                $callable = $value;
                $parameters = $value->getExpectedParameters();
            }
        } catch (OutOfBoundsException $e) { }
        
        if (!isset($callable)) {
            throw new UndefinedFunctionException($node->getName());
        }
        
        $this->pushArgument();

        $this->visitChildrenOf($node, $parameters);

        $arguments = $this->popArgument();

        if ($callable instanceof FuncDef) {
            $result = $this->invoker->invokeFunction(
                $callable,
                new ArgList($arguments),
                $this->context,
                $this
            );
        } elseif ($callable instanceof CallableValue) {
            $result = $this->invoker->invokeCallable(
                $callable,
                new ArgList($arguments),
                $this->context,
                $this
            );
        }
        
        $this->pushArgument($result);
        
        $this->popTrace();
    }

    public function visitIdentifier(Node\Identifier $node)
    {
        try {
            $this->pushArgument($this->context->getSymbolTable()->get($node->getName()));
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

        $this->visitChildrenOf($node, $operator->getParameters());

        $arguments = $this->popArgument();

        $this->pushArgument($this->invoker->invokeFunction(
            $operator,
            new ArgList($arguments),
            $this->context,
            $this
        ));
        
        $this->popTrace();
    }

    public function visitRoot(Node\Root $node)
    {
        $this->visitChildrenOf($node);
    }

    public function visitStatement(Node\Statement $node)
    {
        $this->popArgument();
        $this->visitChildrenOf($node);
    }

    private function pushTrace(Node\Node $node)
    {
        $this->trace->push(new TraceEntry($node->debug()));
    }

    private function popTrace()
    {
        $this->trace->pop();
    }

    /**
     * @param Node\ParentNode $node
     * @param Parameter[]     $parameters
     */
    private function visitChildrenOf(Node\ParentNode $node, array $parameters = [])
    {
        foreach ($node->getChildren() as $i => $child) {
            $parameter = isset($parameters[$i]) ? $parameters[$i] : null;
            
            if ($parameter instanceof Parameter) {
                if ($parameter->isType() && ($child instanceof Node\Identifier)) {
                    $this->pushArgument(new TypeIdentifier($child->getName()));
                    continue;
                }

                if (($parameter->isVariable()) && ($child instanceof Node\Identifier)) {
                    $this->pushArgument(new Variable($child->getName()));
                    continue;
                }

                if ($parameter->isRoutine() && ($child instanceof Node\Routine)) {
                    $this->pushArgument(new Routine($child));
                    continue;
                }

                if ($parameter->isExpression() && ($child instanceof Node\Expression)) {
                    $this->pushArgument(new Expression($child));
                    continue;
                }
                
                if ($parameter->isCollection() && ($child instanceof Node\Container)) {
                    $collection = new Collection($child);
                    
                    if ($parameter->getParameterType() !== Parameter::TYPE_EXPRESSION) {
                        throw new \RuntimeException('TODO: parameter collection beyond expression are not supported');
                    }
                    
                    foreach ($child->getChildren() as $containerChild) {
                        $collection->addArgument(new Expression($containerChild));
                    }
                    
                    $this->pushArgument($collection);
                    continue;
                }
            }

            $child->accept($this);
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
