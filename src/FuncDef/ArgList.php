<?php

namespace Ehimen\Jaslang\FuncDef;

use Ehimen\Jaslang\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Value\Num;
use Ehimen\Jaslang\Value\Str;
use Ehimen\Jaslang\Value\Value;

class ArgList
{
    /**
     * @var Value[]
     */
    private $args = [];
    
    /**
     * @param Value[] $args
     */
    public function __construct(array $args)
    {
        $this->args = $args;
    }

    /**
     * @return Value|null
     */
    public function get($index, $type = null, $allowNull = false)
    {
        $value = isset($this->args[$index]) ? $this->args[$index] : null;
        
        if ($type && ((null !== $value) || !$allowNull)) {
            if (!ArgDef::isOfType($type, $value)) {
                throw new InvalidArgumentException($index, $type, $value);
            }
        }
        
        return $value;
    }

    /**
     * @return Num|null
     */
    public function getNumber($index, $optional = false)
    {
        return $this->get($index, ArgDef::NUMBER, $optional);
    }

    /**
     * @return Str|null
     */
    public function getString($index, $optional = false)
    {
        return $this->get($index, ArgDef::STRING, $optional);
    }
}