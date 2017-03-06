<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\Ast\Visitor;
use Ehimen\Jaslang\Engine\Exception\InvalidArgumentException;

/**
 * Contains a single node in the AST.
 *
 * This is output by our parser to signify parenthesis grouping.
 */
class Container implements ParentNode, Expression
{
    /**
     * @var Node[]
     */
    private $contained = [];
    
    public function debug()
    {
        return implode(', ', array_map(function ($contained) { return $contained->debug(); }, $this->contained));
    }

    public function addChild(Node $child)
    {
        $this->contained[] = $child;
    }

    public function getChildren()
    {
        
        return $this->contained;
    }

    public function getLastChild()
    {
        return end($this->contained);
    }

    public function removeLastChild()
    {
        $this->contained = array_slice($this->contained, 0, -1);
    }

    public function accept(Visitor $visitor)
    {
        $visitor->visitContainer($this);
    }
}
