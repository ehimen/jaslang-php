<?php

namespace Ehimen\Jaslang\Evaluator;

use Ehimen\Jaslang\Ast\BinaryOperation\AdditionOperation;
use Ehimen\Jaslang\Ast\FunctionCall;
use Ehimen\Jaslang\Ast\Node;
use Ehimen\Jaslang\Ast\NumberLiteral;
use Ehimen\Jaslang\Ast\StringLiteral;
use Ehimen\Jaslang\Evaluator\Exception\UndefinedFunctionException;
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
        
        return $this->evaluateNode($ast)->toString();
    }

    private function evaluateNode(Node $node)
    {
        if ($node instanceof StringLiteral) {
            return new Str($node->getValue());
        }
        
        if ($node instanceof NumberLiteral) {
            return new Num($node->getValue());
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
            
            return $this->invoker->invoke($funcDef, new ArgList($arguments));
        }
        
        if ($node instanceof AdditionOperation) {
            $lhs = $this->evaluateNode($node->getLhs())->getValue();
            $rhs = $this->evaluateNode($node->getRhs())->getValue();
            
            return new Num($lhs + $rhs);
        }
        
        throw new InvalidArgumentException(sprintf(
            'Evaluator cannot handle node of type %s',
            get_class($node)
        ));
    }
}