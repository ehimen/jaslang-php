<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

class TypeIdentifier implements Argument
{
    /**
     * @var string
     */
    private $identifier;

    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}
