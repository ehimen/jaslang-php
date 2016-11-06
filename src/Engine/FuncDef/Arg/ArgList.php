<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Value\Value;

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
     * @param mixed $index
     *
     * @return bool
     */
    public function has($index)
    {
        return array_key_exists($index, $this->args);
    }

    /**
     * @param mixed $index
     *
     * @return Value|null
     */
    public function get($index)
    {
        return isset($this->args[$index]) ? $this->args[$index] : null;
    }
}
