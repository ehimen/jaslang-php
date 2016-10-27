<?php

namespace Ehimen\Jaslang\Evaluator;

use Ehimen\Jaslang\Ast\BinaryOperation;
use Ehimen\Jaslang\Ast\BinaryOperation\AdditionOperation;
use Ehimen\Jaslang\Ast\BooleanLiteral;
use Ehimen\Jaslang\Ast\Container;
use Ehimen\Jaslang\Ast\FunctionCall;
use Ehimen\Jaslang\Ast\Literal;
use Ehimen\Jaslang\Ast\Node;
use Ehimen\Jaslang\Ast\NumberLiteral;
use Ehimen\Jaslang\Ast\ParentNode;
use Ehimen\Jaslang\Ast\StringLiteral;
use Ehimen\Jaslang\Evaluator\Context\NullContext;
use Ehimen\Jaslang\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Evaluator\Exception\UndefinedFunctionException;
use Ehimen\Jaslang\Evaluator\Exception\UndefinedOperatorException;
use Ehimen\Jaslang\Evaluator\Trace\EvaluationTrace;
use Ehimen\Jaslang\Evaluator\Trace\TraceEntry;
use Ehimen\Jaslang\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Exception\OutOfBoundsException;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\Evaluator\FunctionRepository;
use Ehimen\Jaslang\Parser\Parser;
use Ehimen\Jaslang\Value\Boolean;
use Ehimen\Jaslang\Value\Num;
use Ehimen\Jaslang\Value\Str;

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

    private $evaluationContext;

    public function __construct(Parser $parser, FunctionRepository $repository, Invoker $invoker)
    {
        $this->parser = $parser;
        $this->repository = $repository;
        $this->invoker = $invoker;
        $this->evaluationContext = new NullContext();
    }

    /**
     * @param $input
     * 
     * @return string
     */
    public function evaluate($input)
    {
        $ast = $this->parser->parse($input);
        
        $this->trace = new EvaluationTrace();

        $result = '';
        
        try {
            foreach ($ast->getChildren() as $statement) {
                $result = $this->evaluateNode($statement)->toString();
            }
        } catch (RuntimeException $e) {
            $e->setEvaluationTrace($this->trace);
            $e->setInput($input);
            throw $e;
        }

        return $result;
    }

    private function evaluateNode(Node $node)
    {


        if ($node instanceof Container) {
            // Special case for a contained node, evaluate the wrapped
            // node, skipping any stack trace handling etc.
            // A contained node only exists to greatly simplify
            // parsing grouping parentheses.
            // TODO: ideally it shouldn't be in the parsed AST?
            return $this->evaluateNode($node->getLastChild());
        }
        
        if ($node instanceof ParentNode) {
            $this->trace->push(new TraceEntry($node->debug()));
        }
        
        if ($node instanceof StringLiteral) {
            $result = new Str($node->getValue());
        }
        
        if ($node instanceof NumberLiteral) {
            $result = new Num($node->getValue());
        }
        
        if ($node instanceof BooleanLiteral) {
            $result = new Boolean($node->getValue());
        }
        
        if ($node instanceof FunctionCall) {
            $arguments = [];
            
            foreach ($node->getArguments() as $argument) {
                $arguments[] = $this->evaluateNode($argument);
            }
            
            try {
                $funcDef = $this->repository->getFunction($node->getName());
            } catch (OutOfBoundsException $e) {
                throw new UndefinedFunctionException($node->getName());
            }
            
            $result = $this->invoker->invokeFunction($funcDef, new ArgList($arguments), $this->evaluationContext);
        }
        
        if ($node instanceof BinaryOperation) {
            try {
                $operator = $this->repository->getOperator($node->getOperator());
            } catch (OutOfBoundsException $e) {
                throw new UndefinedOperatorException($node->getOperator());
            }
            
            if (!$node->getLhs()) {
                throw new InvalidArgumentException('Cannot evaluate binary operator as its left operand is missing!');
            }
            
            if (!$node->getRhs()) {
                throw new InvalidArgumentException('Cannot evaluate binary operator as its right operand is missing!');
            }
            
            $lhs = $this->evaluateNode($node->getLhs());
            $rhs = $this->evaluateNode($node->getRhs());

            $result = $this->invoker->invokeFunction($operator, new ArgList([$lhs, $rhs]), $this->evaluationContext);
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
}
