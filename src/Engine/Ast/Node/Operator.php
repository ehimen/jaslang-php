<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\Ast\Visitor;
use Ehimen\Jaslang\Engine\FuncDef\FunctionRepository;
use Ehimen\Jaslang\Engine\FuncDef\OperatorSignature;

class Operator extends UnlimitedChildrenParentNode implements Expression, PrecedenceRespectingNode
{
    private $operator;

    private $signature;

    /**
     * @param string $operator
     * @param OperatorSignature $signature
     */
    public function __construct($operator, OperatorSignature $signature)
    {
        $this->operator = $operator;
        $this->signature = $signature;
        parent::__construct([]);
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function canBeClosed()
    {
        foreach ($this->getChildren() as $child) {
            if (($child instanceof static) && !$child->canBeClosed()) {
                return false;
            }
        }

        // TODO: should be exactly equal? Should be isValid() or something?
        return count($this->getChildren()) >= $this->getExpectedArgCount();
    }

    public function debug()
    {
        $leftParts  = [];
        $rightParts = [];

        foreach ($this->getChildren() as $i => $child) {
            if (($i === 0) && $this->getSignature()->hasLeftArg()) {
                $leftParts[] = $child->debug();
            } else {
                $rightParts[] = $child->debug();
            }
        }

        return sprintf(
            '%s%s%s%s%s',
            implode(' ', $leftParts),
            empty($leftParts) ? '' : ' ',
            $this->operator,
            empty($rightParts) ? '' : ' ',
            implode(' ', $rightParts)
        );
    }

    /**
     * @return int
     */
    private function getExpectedArgCount()
    {
        return $this->signature->hasLeftArg() + $this->signature->getRightArgs();
    }


    public function accept(Visitor $visitor)
    {
        $visitor->visitOperator($this);
    }
}
