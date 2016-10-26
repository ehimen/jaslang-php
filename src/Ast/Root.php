<?php

namespace Ehimen\Jaslang\Ast;

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
        // TODO: throw.
        return reset($this->getChildren());
    }
}
