<?php

namespace Ehimen\JaslangTestResources\CustomType;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgDef;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Core\Value\Boolean;

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