<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\Ast\Visitor;
use Ehimen\Jaslang\Engine\Exception\OutOfBoundsException;

/**
 * A base node for all ASTs.
 */
class Root extends UnlimitedChildrenParentNode
{
    /**
     * @return Node
     */
    public function getFirstChild()
    {
        $child = current($this->getChildren());

        if (!($child instanceof Node)) {
            throw new OutOfBoundsException('Cannot get first child Root node does not contain any children.');
        }

        return $child;
    }

    public function accept(Visitor $visitor)
    {
        return $visitor->visitRoot($this);
    }
}
