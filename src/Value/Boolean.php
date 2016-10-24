<?php

namespace Ehimen\Jaslang\Value;

class Boolean extends Native
{
    public function __construct($value)
    {
        $value = is_string($value) ? ('true' === strtolower($value)) : $value;
        
        parent::__construct($value);
    }

    public function toString()
    {
        return $this->getValue() ? 'true' : 'false';
    }
}