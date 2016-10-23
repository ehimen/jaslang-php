<?php

namespace Ehimen\Jaslang\Ast;

abstract class Literal implements Node
{
    private $value;
    
    public function __construct($value)
    {
        $this->value = $value;
    }
}