<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\Ast\Visitor;

class FunctionCall extends UnlimitedChildrenParentNode
{
    /**
     * @var string
     */
    private $name;

    public function __construct($name, array $children = [])
    {
        parent::__construct($children);
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getArguments()
    {
        return $this->getChildren();
    }

    public function debug()
    {
        return $this->name . parent::debug();
    }

    public function accept(Visitor $visitor)
    {
        $visitor->visitFunctionCall($this);
    }
}
