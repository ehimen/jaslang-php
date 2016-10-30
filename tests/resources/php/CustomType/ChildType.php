<?php

namespace Ehimen\JaslangTestResources\CustomType;

use Ehimen\Jaslang\Type\ConcreteType;
use Ehimen\Jaslang\Value\Value;

class ChildType implements ConcreteType
{
    public function createValue($value)
    {
        return new ChildValue();
    }

    public function appliesToValue(Value $value)
    {
        return ($value instanceof ChildValue);
    }

    public function appliesToToken(array $token)
    {
        return ($token['value'] === 'c');
    }

    public function getStringForValue($value)
    {
        return 'c';
    }

    public function getParent()
    {
        return new ParentType();
    }

    public function getLiteralPattern()
    {
        return '^c$';
    }
}