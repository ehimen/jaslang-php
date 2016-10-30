<?php

namespace Ehimen\Jaslang\FuncDef;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Type\ConcreteType;
use Ehimen\Jaslang\Value\Value;

/**
 * A function that takes exactly two arguments.
 *
 * This can be used when implementing binary operators, though there is nothing
 * forcing this to be registered as an operator; you can register this as a
 * normal function.
 */
abstract class BinaryFunction implements FuncDef
{
    public function getArgDefs()
    {
        return [
            new ArgDef($this->getLeftArgType(), false),
            new ArgDef($this->getRightArgType(), false),
        ];
    }

    public function invoke(ArgList $operands, EvaluationContext $context)
    {
        return $this->performOperation(
            $operands->get(0),
            $operands->get(1)
        );
    }

    /**
     * @return ConcreteType
     */
    abstract protected function getLeftArgType();

    /**
     * @return ConcreteType
     */
    abstract protected function getRightArgType();

    abstract protected function performOperation(Value $left, Value $right);
}
