<?php

namespace Ehimen\Jaslang\Type;

use Ehimen\Jaslang\Lexer\Lexer;
use Ehimen\Jaslang\Value\Str as StrValue;
use Ehimen\Jaslang\Value\Value;

class Str implements ConcreteType 
{
    public function createValue($value)
    {
        return new StrValue($value);
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
        return ($value instanceof StrValue);
    }

    public function appliesToToken(array $token)
    {
        return ($token['type'] === Lexer::TOKEN_LITERAL_STRING);
    }

    public function getStringForValue($value)
    {
        return sprintf('"%s"', $value);
    }

    /**
     * {@inheritdoc}
     * 
     * Strings are a special case and require special handling in the lexer around escaping.
     * Thus, strings have their own dedicated token and handling is hardcoded in the lexer.
     * We don't need to capture anything more. 
     */
    public function getLiteralPattern()
    {
        return null;
    }
}