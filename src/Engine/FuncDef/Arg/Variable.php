<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

class Variable implements Argument
{
    /**
     * @var string
     */
    private $identifier;

    private $type;

    public function __construct($identifier, TypeIdentifier $type)
    {
        $this->identifier = $identifier;
        $this->type       = $type;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getType()
    {
        return $this->type;
    }
}
