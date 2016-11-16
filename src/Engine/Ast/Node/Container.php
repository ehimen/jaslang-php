<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\Ast\Visitor;
use Ehimen\Jaslang\Engine\Exception\InvalidArgumentException;

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

    public function getLastChild()
    {
        return $this->contained;
    }

    public function removeLastChild()
    {
        $this->contained = null;
    }

    public function accept(Visitor $visitor)
    {
        $visitor->visitContainer($this);
    }
}
