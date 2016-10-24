<?php

namespace Ehimen\Jaslang\Ast;

class BooleanLiteral extends Literal 
{
    public function debug()
    {
        $value = strtolower($this->getValue());
        
        return $value;
    }
}