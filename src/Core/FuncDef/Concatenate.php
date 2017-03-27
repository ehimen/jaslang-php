<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Core\Value\Str;
use Ehimen\Jaslang\Engine\Value\Value;

/**
 * Concatenates two strings.
 */
class Concatenate extends BinaryFunction
{
    /**
     * @inheritdoc
     */
    protected function getLeftArgType()
    {
        return new Type\Str();
    }

    /**
     * @inheritdoc
     */
    protected function getRightArgType()
    {
        return new Type\Str();
    }

    /**
     * @inheritdoc
     */
    protected function performOperation(Value $left, Value $right)
    {
        /** @var Str $left */
        /** @var Str $right */

        return new Str($left->getValue() . $right->getValue());
    }

    public function concatenate(Evaluator $evaluator, Str $left, Str $right)
    {
        return new Str($left->getValue() . $right->getValue());
    }
}
