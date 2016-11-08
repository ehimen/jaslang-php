<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Exception;

class UndefinedSymbolException extends RuntimeException
{
    private $identifier;

    public function __construct($identifier)
    {
        parent::__construct('Undefined symbol: ' . $identifier);
        $this->identifier = $identifier;
    }
}