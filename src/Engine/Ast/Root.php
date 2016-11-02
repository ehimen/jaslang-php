<?php

namespace Ehimen\Jaslang\Engine\Ast;

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
        $child = reset($this->getChildren());

        if (!($child instanceof Node)) {
            throw new OutOfBoundsException('Cannot get first child Root node does not contain any children.');
        }

        return $child;
    }
}
