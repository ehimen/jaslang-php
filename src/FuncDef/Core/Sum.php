<?php

namespace Ehimen\Jaslang\FuncDef\Core;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\BinaryFunction;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Value\Num;
use Ehimen\Jaslang\Value\Value;

class Sum extends BinaryFunction
{
    protected function getLeftArgType()
    {
        return ArgDef::NUMBER;
    }

    protected function getRightArgType()
    {
        return ArgDef::NUMBER;
    }

    protected function performOperation(Value $left, Value $right)
    {
        /** @var Num $left */
        /** @var Num $right */
        return new Num($left->getValue() + $right->getValue());
    }
}
