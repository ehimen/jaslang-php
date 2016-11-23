<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

/**
 * Represents the lack of an argument.
 * 
 * This differs from no argument provided in that this
 * represents absence of a return value from some
 * operation.
 * 
 * Operations which have no return can return this.
 */
class Void implements Argument
{
    public function toString()
    {
        return '[void]';
    }
}
