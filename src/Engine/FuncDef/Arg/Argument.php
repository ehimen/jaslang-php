<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

/**
 * Something which is passed in to a function.
 *
 * This is a tag interface as each type of argument is handled differently.
 * An argument can be a:
 * - Type identifier
 * - Variable
 * - Value
 * And more, since this got more complicated.
 */
interface Argument
{
    /**
     * Gets the PHP string representation of this argument.
     *
     * Note this should not be used in Jaslang evaluation to convert to string.
     *
     * @return string
     */
    public function toString();
}
