<?php

namespace Ehimen\JaslangTestResources;

use Ehimen\Jaslang\FuncDef\Arg\ArgDef;
use Ehimen\Jaslang\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Value\Core\Boolean;
use Ehimen\Jaslang\Value\Core\Str;
use Ehimen\Jaslang\Value\Value;
use Ehimen\Jaslang\Type;

/**
 * Returns true if both operands are the string foo.
 */
class FooOperator extends BinaryFunction
{
    protected function getLeftArgType()
    {
        return new Type\Core\Str();
    }

    protected function getRightArgType()
    {
        return new Type\Core\Str();
    }

    protected function performOperation(Value $left, Value $right)
    {
        /** @var Str $left */
        /** @var Str $right */
        return new Boolean(($left->getValue() === 'foo') && ($right->getValue() === 'foo'));
    }
}
