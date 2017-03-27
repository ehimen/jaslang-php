<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Type\Any;
use Ehimen\Jaslang\Core\Value\ExplicitReturn;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\Value\Value;

/**
 * Operator which simply wraps its sole argument in a special value.
 * 
 * This exists to distinguish between an expression returning its value implicitly
 * and an explicit "return" statement.
 */
class ReturnVal implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::value(new Any()),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        return new ExplicitReturn($args->get(0));
    }

    public function returnValue(Evaluator $evaluator, Value $value)
    {
        return new ExplicitReturn($value);
    }
}
