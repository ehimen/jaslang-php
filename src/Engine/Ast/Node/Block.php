<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\Ast\Visitor;

/**
 * Contains one or more statements.
 */
class Block extends UnlimitedChildrenParentNode
{
    public function accept(Visitor $visitor)
    {
        $visitor->visitBlock($this);
    }
}
