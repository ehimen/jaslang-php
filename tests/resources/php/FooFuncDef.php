<?php

namespace Ehimen\JaslangTestResources;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Value\Str;

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
