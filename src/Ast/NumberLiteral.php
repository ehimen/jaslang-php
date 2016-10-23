<?php

namespace Ehimen\Jaslang\Ast;

class NumberLiteral extends Literal
{
    public function debug()
    {
        return $this->getValue();
    }
}