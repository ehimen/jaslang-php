<?php

namespace Ehimen\Jaslang\Ast\BinaryOperation;

class AdditionOperation extends BinaryOperation
{
    public function debug()
    {
        return sprintf('%s + %s', $this->getLhs()->debug(), $this->getRhs()->debug());
    }
}