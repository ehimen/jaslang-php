<?php

namespace Ehimen\Jaslang\Engine\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\Type\ConcreteType;
use Ehimen\Jaslang\Engine\Value\Value;

/**
 * A function that takes exactly two arguments.
 *
 * This can be used when implementing binary operators, though there is nothing
 * forcing this to be registered as an operator; you can register this as a
 * normal function.
 */
abstract class BinaryFunction implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::value($this->getLeftArgType(), false),
            Parameter::value($this->getRightArgType(), false),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        return $this->performOperation(
            $args->get(0),
            $args->get(1)
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
