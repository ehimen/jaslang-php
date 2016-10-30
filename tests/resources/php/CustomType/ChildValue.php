<?php

namespace Ehimen\JaslangTestResources\CustomType;

use Ehimen\Jaslang\Value\Value;

class ChildValue implements Value
{
    public function toString()
    {
        return 'test-value';
    }

    public function isIdenticalTo(Value $other)
    {
        return ($other instanceof $this);
    }
}