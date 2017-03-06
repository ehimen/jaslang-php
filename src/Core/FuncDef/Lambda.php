<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;

class Lambda implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::collection(Parameter::TYPE_EXPRESSION),
            Parameter::routine(),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        1 === 1;
        // TODO: add invokable type to type repository.
    }
}
