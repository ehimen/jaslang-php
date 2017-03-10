<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

/**
 * A node of the AST that supports children.
 */
interface ParentNode extends Node
{
    /**
     * Adds a child to this node.
     * 
     * If $replacePrevious is true, the last child should be removed.
     *
     * @param Node $child
     * @param bool $replacePrevious
     *
     * @return mixed
     */
    public function addChild(Node $child, $replacePrevious = false);

    /**
     * @return Node[]
     */
    public function getChildren();

    /**
     * Gets the child most recently added to this node.
     *
     * @return Node|null
     */
    public function getLastChild();

    /**
     * Removes the most-recently-added child.
     *
     * This facilitates operators in our parser which require shifting of the AST
     * as with prefix operators, we encounter the left operand(s) before
     * we encounter the operator itself.
     */
    public function removeLastChild();
}
