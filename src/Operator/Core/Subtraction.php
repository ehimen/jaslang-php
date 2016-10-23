<?php

namespace Ehimen\Jaslang\Operator\Core;

use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\Operator\Binary;
use Ehimen\Jaslang\Value\Num;
use Ehimen\Jaslang\Value\Value;

class Subtraction extends Binary
{
    protected function getLeftArgType()
    {
        return ArgDef::NUMBER;
    }

    protected function getRightArgType()
    {
        return ArgDef::NUMBER;
    }

    protected function performOperation(Value $left, Value $right)
    {
        /** @var Num $left */
        /** @var Num $right */
        return new Num($left->getValue() - $right->getValue());
    }
}