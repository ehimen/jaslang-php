<?php

namespace Ehimen\Jaslang\Engine\Value;

/**
 * A value which can be formatted as a string for the purposes of printing.
 * 
 * Note this is intended for outputting values, rather than string
 * casting.
 */
interface Printable extends Value
{
    /**
     * @return string
     */
    public function printValue();
}
