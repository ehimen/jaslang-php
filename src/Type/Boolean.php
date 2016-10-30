<?php

namespace Ehimen\Jaslang\Type;

use Ehimen\Jaslang\Lexer\Lexer;
use Ehimen\Jaslang\Value\Boolean as BooleanValue;
use Ehimen\Jaslang\Value\Value;

class Boolean implements ConcreteType 
{
    public function createValue($value)
    {
        return new BooleanValue($value);
    }

    public function isA(Type $type)
    {
        return ($this instanceof $type);
    }

    public function getParent()
    {
        return new Any();
    }

    public function appliesToValue(Value $value)
    {
        return ($value instanceof BooleanValue);
    }

    public function appliesToToken(array $token)
    {
        return ($token['type'] === Lexer::TOKEN_LITERAL_BOOLEAN);
    }

    public function getStringForValue($value)
    {
        return strtolower($value);
    }

    public function getLiteralPattern()
    {
        // TODO: This is hardcoded in to the lexer. Could detach this for bools?
        return null;
    }
}