<?php

namespace Ehimen\Jaslang\FuncDef\Core;

use Ehimen\Jaslang\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Type;
use Ehimen\Jaslang\Value\Core\Boolean;
use Ehimen\Jaslang\Value\Value;

/**
 * Are two operands identical?
 * 
 * This is the === operator in PHP.
 */
class Identity extends BinaryFunction
{
    protected function getLeftArgType()
    {
        return new Type\Core\Any();
    }

    protected function getRightArgType()
    {
        return new Type\Core\Any();
    }

    protected function performOperation(Value $left, Value $right)
    {
        return new Boolean($left->isIdenticalTo($right));
    }
}
