<?php

namespace Ehimen\Jaslang\Engine\Evaluator\SymbolTable;

use Ehimen\Jaslang\Engine\Exception\OutOfBoundsException;
use Ehimen\Jaslang\Engine\Value\Value;

class SymbolTable
{
    /**
     * @var Value[]
     */
    private $values = [];
    
    public function set($identifier, Value $value)
    {
        $this->values[$identifier] = $value;
    }

    /**
     * @param $identifier
     *
     * @return Value
     */
    public function get($identifier)
    {
        if (!isset($this->values[$identifier])) {
            throw new OutOfBoundsException(sprintf('Symbol table does not contain symbol "%s"', $identifier));
        }
        
        return $this->values[$identifier];
    }
}
