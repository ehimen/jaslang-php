<?php

namespace Ehimen\Jaslang\Evaluator;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Value\Value;

/**
 * Dispatches function calls. Serves as an extension point to provide context to functions being invoked.
 * 
 * Quas wex exort.
 */
interface Invoker
{
    /**
     * @return Value
     */
    public function invokeFunction(FuncDef $function, ArgList $args, EvaluationContext $context);
}
