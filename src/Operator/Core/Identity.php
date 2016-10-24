<?php

namespace Ehimen\Jaslang\Operator\Core;

use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\Operator\Binary;
use Ehimen\Jaslang\Value\Boolean;
use Ehimen\Jaslang\Value\Value;

/**
 * Are two operands identical?
 * 
 * This is the === operator in PHP.
 */
class Identity extends Binary
{
    protected function getLeftArgType()
    {
        return ArgDef::ANY;
    }

    protected function getRightArgType()
    {
        return ArgDef::ANY;
    }

    protected function performOperation(Value $left, Value $right)
    {
        return new Boolean($left->identicalTo($right));
    }
}