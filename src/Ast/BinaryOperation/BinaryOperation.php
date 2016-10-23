<?php

namespace Ehimen\Jaslang\Ast\BinaryOperation;

use Ehimen\Jaslang\Ast\Node;
use Ehimen\Jaslang\Ast\ParentNode;
use Ehimen\Jaslang\Exception\InvalidArgumentException;

abstract class BinaryOperation implements ParentNode
{
    private $lhs;
    
    private $rhs;
    
    public function __construct(Node $lhs = null, Node $rhs = null)
    {
        $this->lhs = $lhs;
        $this->rhs = $rhs;
    }

    public function addChild(Node $child)
    {
        if (!$this->lhs) {
            $this->lhs = $child;
        } elseif (!$this->rhs) {
            $this->rhs = $child;
        } else {
            // TODO: exception handling.
            throw new InvalidArgumentException('Cannot add child as operator already has all children');
        }
    }

    /**
     * @return Node
     */
    public function getLhs()
    {
        return $this->lhs;
    }

    /**
     * @return Node
     */
    public function getRhs()
    {
        return $this->rhs;
    }
}