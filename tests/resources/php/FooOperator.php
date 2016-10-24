<?php

namespace Ehimen\JaslangTestResources;

use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\Operator\Binary;
use Ehimen\Jaslang\Value\Boolean;
use Ehimen\Jaslang\Value\Str;
use Ehimen\Jaslang\Value\Value;

/**
 * Returns true if both operands are the string foo.
 */
class FooOperator extends Binary 
{
    protected function getLeftArgType()
    {
        return ArgDef::STRING;
    }

    protected function getRightArgType()
    {
        return ArgDef::STRING;
    }

    protected function performOperation(Value $left, Value $right)
    {
        /** @var Str $left */
        /** @var Str $right */
        return new Boolean($left->getValue() === 'foo' && $right->getValue() === 'foo');
    }

}