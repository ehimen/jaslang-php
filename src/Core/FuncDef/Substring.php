<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Core\Value;

class Substring implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::value(new Type\Str(), false),
            Parameter::value(new Type\Num(), false),
            Parameter::value(new Type\Num(), false),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context)
    {
        /** @var Value\Str $string */
        $string = $args->get(0);
        /** @var Value\Num $start */
        $start  = $args->get(1);
        /** @var Value\Num $length */
        $length = $args->get(2);
        
        return new Value\Str(substr(
            $string->getValue(),
            $start->getValue(),
            $length->getValue()
        ));
    }
}
