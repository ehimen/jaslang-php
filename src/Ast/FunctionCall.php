<?php

namespace Ehimen\Jaslang\Ast;

class FunctionCall implements ParentNode  
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

    public function addChild(Node $child)
    {
        $this->arguments[] = $child;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getArguments()
    {
        return $this->arguments;
    }
}