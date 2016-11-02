<?php

namespace Ehimen\JaslangTestResources;

use Ehimen\Jaslang\Engine\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Core\Value\Boolean;
use Ehimen\Jaslang\Core\Value\Str;
use Ehimen\Jaslang\Engine\Value\Value;
use Ehimen\Jaslang\Core\Type;

/**
 * Returns true if both operands are the string foo.
 */
class FooOperator extends BinaryFunction
{
    protected function getLeftArgType()
    {
        return new Type\Str();
    }

    protected function getRightArgType()
    {
        return new Type\Str();
    }

    protected function performOperation(Value $left, Value $right)
    {
        /** @var Str $left */
        /** @var Str $right */
        return new Boolean(($left->getValue() === 'foo') && ($right->getValue() === 'foo'));
    }
}
