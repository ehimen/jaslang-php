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

    public function all()
    {
        return $this->args;
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

    public function slice($amount)
    {
        return new static(array_slice($this->args, 0, $amount));
    }

    /**
     * @return Argument|null
     */
    public function getLast()
    {
        return $this->get(count($this->args) - 1);
    }
}
