<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Core\Value\Num;
use Ehimen\Jaslang\Core\Value\Boolean;
use Ehimen\Jaslang\Engine\Value\Value;

class LessThan extends BinaryFunction
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
        return new Boolean($left->getValue() < $right->getValue());
    }
}
