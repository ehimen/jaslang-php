<?php

namespace Ehimen\Jaslang\Core\Type;

use Ehimen\Jaslang\Engine\Lexer\Token;
use Ehimen\Jaslang\Engine\Type\ConcreteType;
use Ehimen\Jaslang\Engine\Type\Type;
use Ehimen\Jaslang\Core\Value\Num as NumValue;
use Ehimen\Jaslang\Engine\Value\Value;

class Num extends BaseType implements ConcreteType
{
    const LITERAL_PATTERN = '[+-]?\d+(?:\.\d*)?';

    public function createValue($value)
    {
        return new NumValue($value);
    }

    public function createEmptyValue()
    {
        return $this->createValue(0);
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
