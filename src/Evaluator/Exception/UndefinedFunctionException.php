<?php

namespace Ehimen\Jaslang\Evaluator\Exception;

class UndefinedFunctionException extends RuntimeException 
{
    private $identifier;
    
    public function __construct($identifier)
    {
        parent::__construct('Undefined function: ' . $identifier);
        $this->identifier = $identifier;
    }
}