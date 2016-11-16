<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\Ast\Visitor;

/**
 * An identifier that doesn't have any meaning until evaluation time.
 * 
 * This is used to describe variables and types in input.
 */
class Identifier implements Node
{
    /**
     * @var string
     */
    private $name;
    
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function debug()
    {
        return $this->name;
    }

    public function accept(Visitor $visitor)
    {
        $visitor->visitIdentifier($this);
    }
}
