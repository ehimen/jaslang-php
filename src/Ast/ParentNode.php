<?php

namespace Ehimen\Jaslang\Ast;

/**
 * A node of the AST that supports children.
 */
interface ParentNode extends Node 
{
    /**
     * Adds a child to this node.
     * 
     * If $replacePrevious is true, this will remove the most-recently-added child.
     * This facilitates operators in our parser which require shifting of the AST
     * as binary operators are infix, thus we encounter the left operand before
     * we encounter the operator itself.
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
}
