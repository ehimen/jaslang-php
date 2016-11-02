<?php

namespace Ehimen\Jaslang\Engine\Ast;

/**
 * A node in the AST.
 */
interface Node
{
    /**
     * Gets the a description of the node as a string for debugging purposes.
     *
     * @return string
     */
    public function debug();
}