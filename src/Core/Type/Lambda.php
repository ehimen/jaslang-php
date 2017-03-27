<?php

namespace Ehimen\Jaslang\Core\Type;

use Ehimen\Jaslang\Core\Value\LambdaExpression;
use Ehimen\Jaslang\Engine\Lexer\Token;
use Ehimen\Jaslang\Engine\Type\ConcreteType;
use Ehimen\Jaslang\Engine\Value\Value;

class Lambda extends BaseType implements ConcreteType
{

    public function createValue($value)
    {
        // TODO: Implement createValue() method.
    }

    public function createEmptyValue()
    {
        return LambdaExpression::voidExpr();
    }

    public function appliesToValue(Value $value)
    {
        return ($value instanceof LambdaExpression);
    }

    public function appliesToToken(Token $token)
    {
        return false;
    }

    public function getStringForValue($value)
    {
        return '';
    }

    public function getLiteralPattern()
    {
        return null;
    }
}
