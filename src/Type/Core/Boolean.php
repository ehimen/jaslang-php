<?php

namespace Ehimen\Jaslang\Type\Core;

use Ehimen\Jaslang\Lexer\Token;
use Ehimen\Jaslang\Type\ConcreteType;
use Ehimen\Jaslang\Type\Type;
use Ehimen\Jaslang\Value\Boolean as BooleanValue;
use Ehimen\Jaslang\Value\Value;

class Boolean implements ConcreteType 
{
    const LITERAL_PATTERN = '^[tT][rR][uU][eE]|[fF][aA][lL][sS][eE]$';      // Insensitive, regardless regex modifiers.

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

    public function appliesToToken(Token $token)
    {
        return in_array(strtolower($token->getValue()), ['true', 'false'], true);
    }

    public function getStringForValue($value)
    {
        return strtolower($value);
    }

    public function getLiteralPattern()
    {
        return static::LITERAL_PATTERN;
    }
}