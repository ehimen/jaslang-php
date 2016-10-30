<?php

namespace Ehimen\Jaslang\Type\Core;

use Ehimen\Jaslang\Lexer\Token;
use Ehimen\Jaslang\Type\ConcreteType;
use Ehimen\Jaslang\Type\Type;
use Ehimen\Jaslang\Value\Num as NumValue;
use Ehimen\Jaslang\Value\Value;

class Num implements ConcreteType
{
    const LITERAL_PATTERN = '[+-]?\d+(?:\.\d*)?';

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

    public function appliesToToken(Token $token)
    {
        return is_numeric($token->getValue());
    }

    public function getStringForValue($value)
    {
        return $value;
    }

    public function getLiteralPattern()
    {
        return static::LITERAL_PATTERN;
    }
}