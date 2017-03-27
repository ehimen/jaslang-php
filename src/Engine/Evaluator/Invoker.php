<?php

namespace Ehimen\Jaslang\Engine\Evaluator;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\Value\CallableValue;
use Ehimen\Jaslang\Engine\Value\Value;

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
    public function invokeFunction(FuncDef $function, ArgList $args, Evaluator $evaluator);

    /**
     * TODO: could merge this and invokeFunction(). Create interface for something that is invokable, whether it
     * TODO: is a native function or a userland callable.
     * 
     * @return Value
     */
    public function invokeCallable(CallableValue $type, ArgList $args, Evaluator $evaluator);
}
