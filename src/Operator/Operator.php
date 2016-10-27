<?php

namespace Ehimen\Jaslang\Operator;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\FuncDef\ArgList;

interface Operator
{
    /**
     * @return ArgDef[]
     */
    public function getArgDefs();

    public function invoke(ArgList $operands, EvaluationContext $context);
}
