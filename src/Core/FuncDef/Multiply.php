<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Value\Num;
use Ehimen\Jaslang\Engine\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Engine\Value\Value;

class Multiply extends BinaryFunction
{
    protected function getLeftArgType()
    {
        return new Type\Num();
    }

    protected function getRightArgType()
    {
        return new Type\Num();
    }

    protected function performOperation(Value $left, Value $right)
    {
        /** @var Num $left */
        /** @var Num $right */
        return new Num($left->getValue() * $right->getValue());
    }
}