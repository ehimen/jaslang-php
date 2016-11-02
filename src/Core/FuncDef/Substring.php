<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgDef;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Core\Value;

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
