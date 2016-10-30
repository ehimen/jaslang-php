<?php

namespace Ehimen\Jaslang\FuncDef\Core;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Type\Num as NumType;
use Ehimen\Jaslang\Value\Num;

class Random implements FuncDef
{
    public function getArgDefs()
    {
        return [
            new ArgDef(new NumType(), true),
            new ArgDef(new NumType(), true),
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
