<?php

namespace Ehimen\JaslangTestResources\CustomType;

use Ehimen\Jaslang\Engine\Lexer\Token;
use Ehimen\Jaslang\Engine\Type\ConcreteType;
use Ehimen\Jaslang\Engine\Value\Value;

class ChildType implements ConcreteType
{
    public function createValue($value)
    {
        return new ChildValue();
    }

    public function createEmptyValue()
    {
        return $this-$this->createValue(null);
    }

    public function appliesToValue(Value $value)
    {
        return ($value instanceof ChildValue);
    }

    public function appliesToToken(Token $token)
    {
        return ($token->getValue() === 'c');
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