<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Type\Type;

class Variable implements Argument
{
    /**
     * @var string
     */
    private $identifier;

    private $type;

    public function __construct($identifier, Type $type)
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

    public function getType()
    {
        return $this->type;
    }
}
