<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\Ast\Visitor;
use Ehimen\Jaslang\Engine\FuncDef\ListOperatorSignature;

/**
 * A tuple is a special kind of node which accepts contains zero or more children.
 * 
 * This is a variable-length construct, which is wrapped by special characters.
 * 
 * An example might be a square bracket enclosure.
 * 
 * [13, 14, 15]
 *
 * This would be represented by a "[]" tuple node, which would have three children:
 * 13, 14 and 15.
 */
class Tuple extends UnlimitedChildrenParentNode implements PrecedenceRespectingNode
{
    /**
     * @var ListOperatorSignature
     */
    private $signature;

    public function __construct(ListOperatorSignature $signature, array $children = [])
    {
        parent::__construct($children);

        $this->signature = $signature;
    }

    public function debug()
    {
        return $this->signature->getEnclosureStart() . substr(parent::debug(), 1, -1) . $this->signature->getEnclosureEnd();
    }

    public function accept(Visitor $visitor)
    {
        $visitor->visitTuple($this);
    }

    /**
     * @return string
     */
    public function getEnclosureEnd()
    {
        return $this->signature->getEnclosureEnd();
    }

    public function getSignature()
    {
        return $this->signature;
    }
}
