<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Value\LambdaExpression;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Collection;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Routine;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;

class Lambda implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::collection(Parameter::TYPE_EXPRESSION),
            Parameter::routine(),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        /** @var Collection $parameterArgs */
        $parameterArgs = $args->get(0);
        $parameters = [];
        
        foreach ($parameterArgs->getExpressions() as $parameter) {
            $parameters[] = $evaluator->evaluateInIsolation($parameter->getExpression());
        }
        
        /** @var Routine $block */
        $block = $args->get(1);
        
        return new LambdaExpression($parameters, $block);
    }
}
