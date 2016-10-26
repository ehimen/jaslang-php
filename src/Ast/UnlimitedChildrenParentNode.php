<?php

namespace Ehimen\Jaslang\Ast;

abstract class UnlimitedChildrenParentNode implements ParentNode
{
    /**
     * @var Node[]
     */
    private $children;

    public function __construct(array $children = [])
    {
        $this->children = $children;
    }

    public function addChild(Node $child, $replacePrevious = false)
    {
        if ($replacePrevious) {
            array_pop($this->children);
        }

        $this->children[] = $child;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function debug()
    {
        return sprintf(
            '(%s)',
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

    public function getLastChild()
    {
        return end($this->children);
    }
}
