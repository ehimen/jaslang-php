<?php

namespace Ehimen\Jaslang\FuncDef\Core;

use Ehimen\Jaslang\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Value\Core\Num;
use Ehimen\Jaslang\Value\Value;
use Ehimen\Jaslang\Type;

class Subtract extends BinaryFunction
{
    protected function getLeftArgType()
    {
        return new Type\Core\Num();
    }

    protected function getRightArgType()
    {
        return new Type\Core\Num();
    }

    protected function performOperation(Value $left, Value $right)
    {
        /** @var Num $left */
        /** @var Num $right */
        return new Num($left->getValue() - $right->getValue());
    }
}
