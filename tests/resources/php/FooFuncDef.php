<?php

namespace Ehimen\JaslangTestResources;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Value\Core\Str;

/**
 * Returns the string "foo".
 */
class FooFuncDef implements FuncDef
{
    public function getArgDefs()
    {
        return [];
    }

    public function invoke(ArgList $args, EvaluationContext $context)
    {
        return new Str("foo");
    }
}
