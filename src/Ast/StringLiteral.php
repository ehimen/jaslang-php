<?php

namespace Ehimen\Jaslang\Ast;

class StringLiteral extends Literal 
{
    public function debug()
    {
        return sprintf('"%s"', $this->getValue());
    }
}