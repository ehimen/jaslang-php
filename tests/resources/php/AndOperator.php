<?php

namespace Ehimen\JaslangTestResources;

use Ehimen\Jaslang\Core\Value\Boolean;
use Ehimen\Jaslang\Engine\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Engine\Value\Value;

class AndOperator extends BinaryFunction
{
    protected function getLeftArgType()
    {
        return new Type\Boolean();
    }

    protected function getRightArgType()
    {
        return new Type\Boolean();
    }

    protected function performOperation(Value $left, Value $right)
    {
        /** @var \Ehimen\Jaslang\Engine\Value\Boolean $left */
        /** @var \Ehimen\Jaslang\Engine\Value\Boolean $right */
        return new Boolean($left->getValue() && $right->getValue());
    }
}
