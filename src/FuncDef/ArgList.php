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
    public function get($index)
    {
        return isset($this->args[$index]) ? $this->args[$index] : null;
    }

    /**
     * @return Num|null
     */
    public function getNumber($index, $optional = false)
    {
        $arg = $this->get($index);
        
        if (null === $arg && $optional) {
            return null;
        }
        
        if (!($arg instanceof Num)) {
            throw new InvalidArgumentException($index, ArgDef::NUMBER, $arg);
        }
        
        return $arg;
    }

    /**
     * @return Str
     */
    public function getString($index)
    {
        $arg = $this->get($index);
        
        if (!($arg instanceof Str)) {
            throw new InvalidArgumentException($index, ArgDef::STRING, $arg);
        }
        
        return $arg;
    }
}