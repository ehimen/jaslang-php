<?php

namespace Ehimen\Jaslang\Value;

class Num extends Native implements StringLike 
{
    public function castToString()
    {
        return new Str((string)$this->value);
    }
}