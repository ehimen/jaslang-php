<?php

namespace Ehimen\JaslangTestResources;

use Ehimen\Jaslang\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Type;
use Ehimen\Jaslang\Value;

class AndOperator extends BinaryFunction
{
    protected function getLeftArgType()
    {
        return new Type\Core\Boolean();
    }

    protected function getRightArgType()
    {
        return new Type\Core\Boolean();
    }

    protected function performOperation(Value\Value $left, Value\Value $right)
    {
        /** @var Value\Boolean $left */
        /** @var Value\Boolean $right */
        return new Value\Boolean($left->getValue() && $right->getValue());
    }
}
