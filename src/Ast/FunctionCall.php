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

    public function getChildren()
    {
        return $this->arguments;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function debug()
    {
        return sprintf(
            '%s(%s)',
            $this->name,
            implode(
                ', ',
                array_map(
                    function (Node $node) {
                        return $node->debug();
                    },
                    $this->getChildren()
                )
            )
        ); 
    }
}