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

    public function getLastChild()
    {
        if ($this->rhs) {
            return $this->rhs;
        } else {
            return $this->lhs;
        }
    }

    public function addChild(Node $child, $replacePrevious = false)
    {
        if ($replacePrevious) {
            if ($this->rhs) {
                $this->rhs = null;
            } elseif ($this->lhs) {
                $this->lhs = null;
            }
        }

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
     * @return Node|null
     */
    public function getLhs()
    {
        return $this->lhs;
    }

    /**
     * @return Node|null
     */
    public function getRhs()
    {
        return $this->rhs;
    }

    public function hasOperands()
    {
        return ($this->lhs && $this->rhs);
    }

    public function debug()
    {
        $lhs = $this->getLhs() ? $this->getLhs()->debug() : '[empty]';
        $rhs = $this->getRhs() ? $this->getRhs()->debug() : '[empty]';
        
        return sprintf('%s %s %s', $lhs, $this->operator, $rhs);
    }
}
