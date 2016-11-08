<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Core\Type\Num as NumType;
use Ehimen\Jaslang\Core\Value\Num;

class Random implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::value(new NumType(), true),
            Parameter::value(new NumType(), true),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context)
    {
        if (!$args->has(0)) {
            $result = rand();
        } elseif (!$args->has(1)) {
            $result = rand($args->get(0));
        } else {
            $result = rand($args->get(0), $args->get(1));
        }
        
        return new Num($result);
    }
}
