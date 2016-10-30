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
        /** @var \Ehimen\Jaslang\Value\Core\Boolean $left */
        /** @var \Ehimen\Jaslang\Value\Core\Boolean $right */
        return new Value\Core\Boolean($left->getValue() && $right->getValue());
    }
}
