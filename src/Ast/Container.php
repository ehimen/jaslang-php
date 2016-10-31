<?php

namespace Ehimen\Jaslang\Ast;

use Ehimen\Jaslang\Exception\InvalidArgumentException;

/**
 * Contains a single node in the AST.
 * 
 * This is output by our parser to signify parenthesis grouping.
 */
class Container implements ParentNode
{
    /**
     * @var Node
     */
    private $contained;

    public function __construct(Node $contained = null)
    {
        $this->contained = $contained;
    }
    
    public function debug()
    {
        return $this->contained->debug();
    }

    public function addChild(Node $child)
    {
        if ($this->contained) {
            throw new InvalidArgumentException('Cannot add child to a container as it already has one.');
        }
        
        $this->contained = $child;
    }

    public function getChildren()
    {
        if (!$this->contained) {
            return [];
        }
        
        return [$this->contained];
    }

    public function getLastChild($pop = false)
    {
        $child = $this->contained;

        if ($pop) {
            $this->contained = null;
        }

        return $child;
    }

}
