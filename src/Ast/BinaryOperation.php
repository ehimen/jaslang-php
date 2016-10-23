<?php

namespace Ehimen\Jaslang\Ast;

use Ehimen\Jaslang\Exception\InvalidArgumentException;

class BinaryOperation implements ParentNode
{
    private $lhs;
    
    private $rhs;
    
    private $operator;
    
    public function __construct($operator, Node $lhs = null, Node $rhs = null)
    {
        $this->operator = $operator;
        $this->lhs = $lhs;
        $this->rhs = $rhs;
    }

    public function getOperator()
    {
        return $this->operator;
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

    public function getChildren()
    {
        return array_filter([
            $this->lhs,
            $this->rhs,
        ]);
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

    public function debug()
    {
        return sprintf('%s %s %s', $this->getLhs()->debug(), $this->operator, $this->getRhs()->debug());
    }
}