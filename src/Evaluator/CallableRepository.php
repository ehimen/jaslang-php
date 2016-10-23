<?php

namespace Ehimen\Jaslang\Evaluator;

use Ehimen\Jaslang\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Exception\OutOfBoundsException;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Operator\Operator;

class CallableRepository
{
    /**
     * @var FuncDef[]
     */
    private $funcs = [];

    /**
     * @var Operator[]
     */
    private $operators = [];

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

    public function registerOperator($identifier, Operator $operator)
    {
        if (isset($this->operators[$identifier])) {
            throw new InvalidArgumentException(
                'Operator with identifier "%s" is already registered',
                $identifier
            );
        }

        $this->operators[$identifier] = $operator;
    }

    public function getOperator($identifier)
    {
        if (!isset($this->operators[$identifier])) {
            throw new OutOfBoundsException('Operator with identifier "%s" not found');
        }

        return $this->operators[$identifier];
    }
}