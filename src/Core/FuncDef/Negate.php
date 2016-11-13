<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Core\Value;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;

class Negate implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::value(new Type\Boolean()),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context)
    {
        /** @var Value\Boolean $value */
        $value = $args->get(0);
        
        return new Value\Boolean(!$value->getValue());
    }
}
