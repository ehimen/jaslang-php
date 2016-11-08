<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Type\ConcreteType;

class Variable implements Argument
{
    /**
     * @var string
     */
    private $identifier;

    private $type;

    public function __construct($identifier, ConcreteType $type = null)
    {
        $this->identifier = $identifier;
        $this->type       = $type;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function toString()
    {
        return '[variable] ' . $this->identifier;
    }
}
