<?php

namespace Ehimen\Jaslang\Value;

class Str extends Native implements StringLike
{
    public function castToString()
    {
        return $this;
    }
}