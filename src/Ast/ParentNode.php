<?php

namespace Ehimen\Jaslang\Ast;

/**
 * A node of the AST that supports children.
 */
interface ParentNode extends Node 
{
    public function addChild(Node $child, $replacePrevious = false);

    /**
     * @return Node[]
     */
    public function getChildren();

    public function getLastChild();
}
