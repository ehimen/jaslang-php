<?php

namespace Ehimen\Jaslang\Value;

/**
 * A value whose type is native to PHP.
 * 
 * This is convenience for simple types which wrap a native value.
 */
abstract class Native implements Value 
{
    protected $value;
    
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function identicalTo(Value $other)
    {
        if (!is_a($other, self::class)) {
            return false;
        }
        
        return ($this->value === $other->value);
    }


    public function toString()
    {
        return (string)$this->value;
    }
}