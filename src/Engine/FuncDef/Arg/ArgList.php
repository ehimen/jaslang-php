<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

class ArgList
{
    /**
     * @var Argument[]
     */
    private $args = [];
    
    /**
     * @param Argument[] $args
     */
    public function __construct(array $args = [])
    {
        $this->args = $args;
    }

    /**
     * @param int $index
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
     * @return Argument|null
     */
    public function get($index)
    {
        return isset($this->args[$index]) ? $this->args[$index] : null;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->args);
    }
}
