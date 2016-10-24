<?php

namespace Ehimen\Jaslang\Value;

class Num extends Native implements StringLike 
{
    public function __construct($value)
    {
        if (floatval($value) == intval($value)) {
            // If this is true, we can store it as an integer.
            $value = intval($value);
        } else {
            $value = floatval($value);
        }
        
        parent::__construct($value);
    }
    
    public function castToString()
    {
        return new Str((string)$this->value);
    }
}