<?php

namespace Ehimen\Jaslang\Engine\Value;

use Ehimen\Jaslang\Engine\FuncDef\Arg\Argument;

interface Value extends Argument
{

    /**
     * Is this value identical to $other?
     *
     * This is a strict check. Types must match.
     *
     * @param Value $other
     */
    public function isIdenticalTo(Value $other);
}
