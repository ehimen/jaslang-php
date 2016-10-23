<?php

namespace Ehimen\Jaslang\Ast;

class FunctionCall implements Node 
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Node[]
     */
    private $arguments;

    public function __construct($name, array $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function addArgument(Node $node)
    {
        $this->arguments[] = $node;
    }
}