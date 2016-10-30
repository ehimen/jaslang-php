<?php

namespace Ehimen\JaslangTestResources\CustomType;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Value\Boolean;

class ChildFunction implements FuncDef
{
    public function getArgDefs()
    {
        return [
            new ArgDef(new ChildType()),
            new ArgDef(new ParentType())
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context)
    {
        return new Boolean($args->get(0)->isIdenticalTo($args->get(1)));
    }
}