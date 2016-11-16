<?php

namespace Ehimen\Jaslang\Engine\Evaluator;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\Value\Value;

/**
 * Dispatches function calls. Serves as an extension point to provide context to functions being invoked.
 *
 * Quas wex exort.
 */
interface Invoker
{
    /**
     * @param FuncDef           $function
     * @param ArgList           $args
     * @param EvaluationContext $context
     *
     * @return Value
     */
    public function invokeFunction(FuncDef $function, ArgList $args, EvaluationContext $context, Evaluator $evaluator);
}
