<?php

namespace Ehimen\Jaslang\FuncDef\Core;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Value\Num;
use Ehimen\Jaslang\Value\Str;
use Ehimen\Jaslang\Type;

class Substring implements FuncDef
{
    public function getArgDefs()
    {
        return [
            new ArgDef(new Type\Str(), false),
            new ArgDef(new Type\Num(), false),
            new ArgDef(new Type\Num(), false),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context)
    {
        /** @var Str $string */
        $string = $args->get(0);
        /** @var Num $start */
        $start  = $args->get(1);
        /** @var Num $length */
        $length = $args->get(2);
        
        return new Str(substr(
            $string->getValue(),
            $start->getValue(),
            $length->getValue()
        ));
    }
}
