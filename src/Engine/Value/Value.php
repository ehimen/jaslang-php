<?php

namespace Ehimen\Jaslang\Engine\Value;

interface Value
{
    /**
     * Gets the PHP string representation of this value.
     * 
     * Note this should not be used in Jaslang evaluation to convert to string.
     * 
     * @return string
     */
    public function toString();

    /**
     * Is this value identical to $other?
     * 
     * This is a strict check. Types must match.
     * 
     * @param Value $other
     */
    public function isIdenticalTo(Value $other);
}