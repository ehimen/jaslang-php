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

    public function isIdenticalTo(Value $other)
    {
        if (!is_a($other, self::class)) {
            return false;
        }
        
        if ($other instanceof static) {
            return ($this->value === $other->value);
        }
        
        return ($this === $other);
    }


    public function toString()
    {
        return (string)$this->value;
    }
}