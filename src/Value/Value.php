<?php

namespace Ehimen\Jaslang\Value;

interface Value
{
    /**
     * Gets the PHP string representation of this value.
     * 
     * Note this should not be used Jaslang evaluation. For Jaslang string conversion,
     * @see StringLike
     * 
     * @return string
     */
    public function toString();
}