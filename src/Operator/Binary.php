<?php

namespace Ehimen\Jaslang\Operator;

use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\Value\Value;

/**
 * An operation with a left and right-hand side.
 */
abstract class Binary implements Operator
{
    public function getArgDefs()
    {
        return [
            new ArgDef($this->getLeftArgType(), false),
            new ArgDef($this->getRightArgType(), false),
        ];
    }

    public function invoke(ArgList $operands)
    {
        return $this->performOperation(
            $operands->get(0, $this->getLeftArgType()),
            $operands->get(1, $this->getRightArgType())
        );
    }

    abstract protected function getLeftArgType();
    
    abstract protected function getRightArgType();

    abstract protected function performOperation(Value $left, Value $right);
}