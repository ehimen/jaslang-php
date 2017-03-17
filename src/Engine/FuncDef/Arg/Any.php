<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Ast\Node\Node;

class Any implements Argument
{
    /**
     * @var Node
     */
    private $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
    }
    
    public function toString()
    {
        return $this->node->debug();
    }

    public function getNode()
    {
        return $this->node;
    }
}
