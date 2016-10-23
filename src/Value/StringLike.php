<?php

namespace Ehimen\Jaslang\Value;

/**
 * A Jaslang type that can be implicitly cast to string.
 */
interface StringLike
{
    const TYPE = 'string-like';
    
    /**
     * @return Str
     */
    public function castToString();
}