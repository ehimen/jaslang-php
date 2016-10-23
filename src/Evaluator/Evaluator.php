<?php

namespace Ehimen\Jaslang\Evaluator;

use Ehimen\Jaslang\Ast\BinaryOperation\AdditionOperation;
use Ehimen\Jaslang\Ast\FunctionCall;
use Ehimen\Jaslang\Ast\Literal;
use Ehimen\Jaslang\Ast\Node;
use Ehimen\Jaslang\Ast\NumberLiteral;
use Ehimen\Jaslang\Ast\ParentNode;
use Ehimen\Jaslang\Ast\StringLiteral;
use Ehimen\Jaslang\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Evaluator\Exception\UndefinedFunctionException;
use Ehimen\Jaslang\Evaluator\Trace\EvaluationTrace;
use Ehimen\Jaslang\Evaluator\Trace\TraceEntry;
use Ehimen\Jaslang\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Exception\OutOfBoundsException;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\Repository;
use Ehimen\Jaslang\Parser\Parser;
use Ehimen\Jaslang\Value\Num;
use Ehimen\Jaslang\Value\Str;

class Evaluator
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Repository
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

    public function __construct(Parser $parser, Repository $repository, Invoker $invoker)
    {
        $this->parser = $parser;
        $this->repository = $repository;
        $this->invoker = $invoker;
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
        
        try {
            return $this->evaluateNode($ast)->toString();
        } catch (RuntimeException $e) {
            $e->setEvaluationTrace($this->trace);
            $e->setInput($input);
            throw $e;
        }
    }

    private function evaluateNode(Node $node)
    {
        if ($node instanceof ParentNode) {
            $this->trace->push(new TraceEntry($node->debug()));
        }
        
        if ($node instanceof StringLiteral) {
            $result = new Str($node->getValue());
        }
        
        if ($node instanceof NumberLiteral) {
            $result = new Num($node->getValue());
        }
        
        if ($node instanceof FunctionCall) {
            $arguments = [];
            
            foreach ($node->getArguments() as $argument) {
                $arguments[] = $this->evaluateNode($argument);
            }
            
            try {
                $funcDef = $this->repository->getFuncDef($node->getName());
            } catch (OutOfBoundsException $e) {
                throw new UndefinedFunctionException($node->getName());
            }
            
            $result = $this->invoker->invoke($funcDef, new ArgList($arguments));
        }
        
        if ($node instanceof AdditionOperation) {
            $lhs = $this->evaluateNode($node->getLhs())->getValue();
            $rhs = $this->evaluateNode($node->getRhs())->getValue();
            
            $result = new Num($lhs + $rhs);
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