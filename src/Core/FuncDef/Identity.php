<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Core\Value\Boolean;
use Ehimen\Jaslang\Engine\Value\Value;

/**
 * Are two operands identical?
 *
 * This is the === operator in PHP.
 */
class Identity extends BinaryFunction
{
    protected function getLeftArgType()
    {
        return new Type\Any();
    }

    protected function getRightArgType()
    {
        return new Type\Any();
    }

    protected function performOperation(Value $left, Value $right)
    {
        return new Boolean($left->isIdenticalTo($right));
    }
}
