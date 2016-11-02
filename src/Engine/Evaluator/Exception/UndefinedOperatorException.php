<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Exception;

class UndefinedOperatorException extends RuntimeException 
{
    private $identifier;
    
    public function __construct($identifier)
    {
        parent::__construct('Undefined operator: ' . $identifier);
        $this->identifier = $identifier;
    }
}