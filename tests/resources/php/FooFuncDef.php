<?php

namespace Ehimen\JaslangTestResources;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Core\Value\Str;

/**
 * Returns the string "foo".
 */
class FooFuncDef implements FuncDef
{
    public function getParameters()
    {
        return [];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        return new Str("foo");
    }
}
