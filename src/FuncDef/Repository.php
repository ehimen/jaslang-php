<?php

namespace Ehimen\Jaslang\FuncDef;

use Ehimen\Jaslang\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Exception\OutOfBoundsException;

class Repository
{
    private $funcs = [];

    public function registerFuncDef($identifier, FuncDef $func)
    {
        if (isset($this->funcs[$identifier])) {
            throw new InvalidArgumentException(
                'Function with identifier "%s" is already registered',
                $identifier
            );
        }
        
        $this->funcs[$identifier] = $func;
    }

    public function getFuncDef($identifier)
    {
        if (!isset($this->funcs[$identifier])) {
            throw new OutOfBoundsException('Function with identifier "%s" not found');
        }
        
        return $this->funcs[$identifier];
    }
}