<?php

namespace Ehimen\Jaslang\FuncDef\Arg;

use Ehimen\Jaslang\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Type\Type;
use Ehimen\Jaslang\Value\Core\Num;
use Ehimen\Jaslang\Value\Core\Str;
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

    public function has($index)
    {
        return array_key_exists($index, $this->args);
    }

    /**
     * @return Value|null
     */
    public function get($index)
    {
        return isset($this->args[$index]) ? $this->args[$index] : null;
    }
}