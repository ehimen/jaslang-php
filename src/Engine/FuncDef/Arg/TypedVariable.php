<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

/**
 * A special kind of variable which also contains an explicit type reference.
 * 
 * Normal variable arguments don't require a type.
 */
class TypedVariable extends Variable
{
    private $type;
    
    public function __construct($identifier, TypeIdentifier $type)
    {
        parent::__construct($identifier);
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function toString()
    {
        return sprintf('%s (%s)', parent::toString(), $this->type->toString());
    }
}
