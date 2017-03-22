<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Core\Type\Num as NumType;
use Ehimen\Jaslang\Core\Value\Num;
use Ehimen\Jaslang\Engine\Value\Value;

class Sum extends BinaryFunction
{
    protected function getLeftArgType()
    {
        return new NumType();
    }

    protected function getRightArgType()
    {
        return new NumType();
    }

    protected function performOperation(Value $left, Value $right)
    {
        /** @var Num $left */
        /** @var Num $right */
        return new Num($left->getValue() + $right->getValue());
    }

    public function sum(Evaluator $evaluator, Num $left, Num $right)
    {
        return new Num($left->getValue() + $right->getValue());
    }
}
