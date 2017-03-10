<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\Ast\Visitor;
use Ehimen\Jaslang\Engine\Exception\InvalidArgumentException;

/**
 * Contains zero or more single nodes in the AST.
 *
 * This is output by our parser to signify parenthesis grouping.
 */
class Container extends UnlimitedChildrenParentNode implements ParentNode, Expression
{
    public function accept(Visitor $visitor)
    {
        $visitor->visitContainer($this);
    }
}
