<?php

namespace Ehimen\Jaslang\Engine\Evaluator;

use Ehimen\Jaslang\Engine\Ast\Identifier;
use Ehimen\Jaslang\Engine\Ast\Operator;
use Ehimen\Jaslang\Engine\Ast\Container;
use Ehimen\Jaslang\Engine\Ast\FunctionCall;
use Ehimen\Jaslang\Engine\Ast\Literal;
use Ehimen\Jaslang\Engine\Ast\Node;
use Ehimen\Jaslang\Engine\Ast\ParentNode;
use Ehimen\Jaslang\Engine\Ast\Statement;
use Ehimen\Jaslang\Engine\Evaluator\Context\ContextFactory;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Context\NullContext;
use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedFunctionException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedOperatorException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedSymbolException;
use Ehimen\Jaslang\Engine\Evaluator\Trace\EvaluationTrace;
use Ehimen\Jaslang\Engine\Evaluator\Trace\TraceEntry;
use Ehimen\Jaslang\Engine\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\Exception\OutOfBoundsException;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Argument;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeIdentifier;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Variable;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\FuncDef\FunctionRepository;
use Ehimen\Jaslang\Engine\Parser\Parser;
use Ehimen\Jaslang\Engine\Value\Value;

class Evaluator
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var FunctionRepository
     */
    private $repository;

    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var EvaluationTrace
     */
    private $trace;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    public function __construct(
        Parser $parser,
        FunctionRepository $repository,
        Invoker $invoker,
        ContextFactory $contextFactory
    ) {
        $this->parser         = $parser;
        $this->repository     = $repository;
        $this->invoker        = $invoker;
        $this->contextFactory = $contextFactory;
    }

    /**
     * @param $input
     *
     * @return string
     * @throws RuntimeException
     */
    public function evaluate($input)
    {
        $ast = $this->parser->parse($input);
        
        $this->trace = new EvaluationTrace();

        $result = '';
        
        $context = $this->contextFactory->createContext();
        
        try {
            foreach ($ast->getChildren() as $statement) {
                // The evaluator returns the string of the last evaluation.
                $result = $this->evaluateNode($statement, $context)->toString();
            }
        } catch (RuntimeException $e) {
            $e->setEvaluationTrace($this->trace);
            $e->setInput($input);
            throw $e;
        }

        return $result;
    }

    /**
     * @return Argument
     */
    private function evaluateNode(Node $node, EvaluationContext $context)
    {
        if (($node instanceof Container) || ($node instanceof Statement)) {
            // Special case for a single-contained node, evaluate the wrapped
            // node, skipping any stack trace handling etc.
            // This handles parentheses grouping and language statements.
            return $this->evaluateNode($node->getLastChild(), $context);
        }
        
        if ($node instanceof ParentNode) {
            $this->trace->push(new TraceEntry($node->debug()));
        }
        
        if ($node instanceof Literal) {
            $result = $node->getType()->createValue($node->getValue());
        }
        
        if ($node instanceof FunctionCall) {
            // Attempt to get the function definition before evaluating
            // arguments as this allows for early failure.
            try {
                $funcDef = $this->repository->getFunction($node->getName());
            } catch (OutOfBoundsException $e) {
                throw new UndefinedFunctionException($node->getName());
            }

            $arguments = [];

            foreach ($node->getArguments() as $i => $argument) {
                $arguments[] = $this->resolveArgument($argument, $funcDef, $i, $context);
            }
            
            $result = $this->invoker->invokeFunction($funcDef, new ArgList($arguments), $context);
        }
        
        if ($node instanceof Operator) {
            try {
                $operator = $this->repository->getOperator($node->getOperator());
            } catch (OutOfBoundsException $e) {
                throw new UndefinedOperatorException($node->getOperator());
            }

            $arguments = [];

            foreach ($node->getChildren() as $i => $argument) {
                $arguments[] = $this->resolveArgument($argument, $operator, $i, $context);
            }

            $result = $this->invoker->invokeFunction($operator, new ArgList($arguments), $context);
        }
        
        if ($node instanceof ParentNode) {
            $this->trace->pop();
        }
        
        if (isset($result)) {
            return $result;
        }
        
        throw new InvalidArgumentException(sprintf(
            'Evaluator cannot handle node of type %s',
            get_class($node)
        ));
    }

    /**
     * @param Node              $node
     * @param FuncDef           $function
     * @param                   $position
     * @param EvaluationContext $evaluationContext
     *
     * @return Argument
     */
    private function resolveArgument(Node $node, FuncDef $function, $position, EvaluationContext $evaluationContext)
    {
        if ($node instanceof Identifier) {
            $parameters = $function->getParameters();
            $parameter  = isset($parameters[$position]) ? $parameters[$position] : null;
            
            if ($parameter instanceof Parameter) {
                if ($parameter->isType()) {
                    return new TypeIdentifier($node->getName());
                }
                
                if ($parameter->isVariable()) {
                    return new Variable($node->getName());
                }
            }

            try {
                return $evaluationContext->getSymbolTable()->get($node->getName());
            } catch (OutOfBoundsException $e) {
                throw new UndefinedSymbolException($node->getName());
            }
        }
        
        return $this->evaluateNode($node, $evaluationContext);
    }
}
