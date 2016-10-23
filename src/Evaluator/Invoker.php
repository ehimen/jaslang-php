<?php

namespace Ehimen\Jaslang\Evaluator;

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
    public function invoke(FuncDef $function, ArgList $args);
}