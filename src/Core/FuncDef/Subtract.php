<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Core\Value\Num;
use Ehimen\Jaslang\Engine\Value\Value;
use Ehimen\Jaslang\Core\Type;

class Subtract extends BinaryFunction
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
        return new Num($left->getValue() - $right->getValue());
    }

    public function subtract(Evaluator $evaluator, Num $left, Num $right)
    {
        return new Num($left->getValue() - $right->getValue());
    }
}
