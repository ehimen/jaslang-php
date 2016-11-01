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
     * @param Node $child
     * 
     * @return mixed
     */
    public function addChild(Node $child);

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
