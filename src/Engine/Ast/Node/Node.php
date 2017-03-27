<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\Ast\Visitor;

/**
 * A node in the AST.
 */
interface Node extends \JsonSerializable
{
    /**
     * Gets the a description of the node as a string for debugging purposes.
     *
     * @return string
     */
    public function debug();

    public function accept(Visitor $visitor);
}
