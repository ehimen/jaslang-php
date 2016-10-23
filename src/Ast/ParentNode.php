<?php

namespace Ehimen\Jaslang\Ast;

/**
 * A node of the AST that supports children.
 */
interface ParentNode extends Node 
{
    public function addChild(Node $child);

    /**
     * @return Node[]
     */
    public function getChildren();
}