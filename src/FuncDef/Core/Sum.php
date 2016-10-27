<?php

namespace Ehimen\Jaslang\FuncDef\Core;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Value\Num;

class Sum extends FuncDef
{
    public function getArgDefs()
    {
        return [
            new ArgDef(ArgDef::NUMBER, false),
            new ArgDef(ArgDef::NUMBER, false),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context)
    {
        return new Num($args->getNumber(0)->getValue() + $args->getNumber(1)->getValue());
    }
}
