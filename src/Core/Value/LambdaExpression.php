<?php

namespace Ehimen\Jaslang\Core\Value;

use Ehimen\Jaslang\Engine\Ast\Node\Block;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\TypedParameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Routine;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypedVariable;
use Ehimen\Jaslang\Engine\Value\CallableValue;

class LambdaExpression implements CallableValue
{
    /**
     * @var TypedVariable[]
     */
    private $parameters;

    /**
     * @var Routine
     */
    private $body;
    
    public function __construct(array $parameters, Routine $body)
    {
        $this->parameters = $parameters;
        $this->body = $body;
    }


    public static function voidExpr()
    {
        return new static([], new Routine(new Block()));
    }

    public function getExpectedParameters()
    {
        return $this->parameters;
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        foreach ($this->parameters as $i => $parameter) {
            $value = $args->get($i);
            $context->getSymbolTable()->set($parameter->getIdentifier(), $value);
        }
        
        $evaluator->evaluateInIsolation($this->body->getRoutine());
    }


    public function toString()
    {
        return '[lambda]';
    }
}
