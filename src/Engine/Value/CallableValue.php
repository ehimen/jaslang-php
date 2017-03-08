<?php

namespace Ehimen\Jaslang\Engine\Value;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypedVariable;

interface CallableValue extends Value
{
    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator);

    /**
     * @return TypedVariable[]
     */
    public function getExpectedParameters();
}
