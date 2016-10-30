<?php

namespace Ehimen\Jaslang\Value;

interface Value
{
    /**
     * Gets the PHP string representation of this value.
     * 
     * Note this should not be used in Jaslang evaluation. For Jaslang string conversion,
     * @see StringLike
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