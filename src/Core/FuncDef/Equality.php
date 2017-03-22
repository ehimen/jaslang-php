<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Core\Value\Boolean;
use Ehimen\Jaslang\Engine\Value\Value;

/**
 * Are two operands equal?
 * 
 * This is only true if the two operands are the same type and value.
 */
class Equality extends BinaryFunction
{
    protected function getLeftArgType()
    {
        return new Type\Any();
    }

    protected function getRightArgType()
    {
        return new Type\Any();
    }

    protected function performOperation(Value $left, Value $right)
    {
    }

    public function equality(Evaluator $evaluator, Value $left, Value $right)
    {
        return new Boolean(($left instanceof $right) && ($right instanceof $left) && ($left->toString() === $right->toString()));
    }
}
