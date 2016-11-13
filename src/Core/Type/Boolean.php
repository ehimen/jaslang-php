<?php

namespace Ehimen\Jaslang\Core\Type;

use Ehimen\Jaslang\Engine\Lexer\Token;
use Ehimen\Jaslang\Engine\Type\ConcreteType;
use Ehimen\Jaslang\Engine\Type\Type;
use Ehimen\Jaslang\Core\Value\Boolean as BooleanValue;
use Ehimen\Jaslang\Engine\Value\Value;

class Boolean extends BaseType implements ConcreteType
{
    const LITERAL_PATTERN = '^[tT][rR][uU][eE]|[fF][aA][lL][sS][eE]$';      // Insensitive, regardless regex modifiers.

    public function createValue($value)
    {
        return new BooleanValue($value);
    }

    public function createEmptyValue()
    {
        return $this->createValue(false);
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
