<?php

namespace Ehimen\Jaslang\Type;

use Ehimen\Jaslang\Lexer\Lexer;
use Ehimen\Jaslang\Value\Num as NumValue;
use Ehimen\Jaslang\Value\Value;

class Num implements ConcreteType
{
    public function createValue($value)
    {
        return new NumValue($value);
    }

    public function getParent()
    {
        return new Any();
    }

    public function isA(Type $type)
    {
        return ($this instanceof $type);
    }

    public function appliesToValue(Value $value)
    {
        return ($value instanceof NumValue);
    }

    public function appliesToToken(array $token)
    {
        return ($token['type'] === Lexer::TOKEN_LITERAL_NUMBER);
    }

    public function getStringForValue($value)
    {
        return $value;
    }

    public function getLiteralPattern()
    {
        // TODO: This is hardcoded in to the lexer. Could detach this for nums?
        return null;
    }
}