<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\Ast\Visitor;

class Statement extends UnlimitedChildrenParentNode
{
    public function accept(Visitor $visitor)
    {
        $visitor->visitStatement($this);
    }
}
