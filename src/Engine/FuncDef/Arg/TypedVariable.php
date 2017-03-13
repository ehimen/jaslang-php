<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\Type\Type;

/**
 * A special kind of variable which also contains an explicit type reference.
 * 
 * Normal variable arguments don't require a type.
 */
class TypedVariable extends Variable
{
    private $typeIdentifier;
    
    private $type;
    
    public function __construct($identifier, Type $type, TypeIdentifier $typeIdentifier = null)
    {
        parent::__construct($identifier);
        
        $this->typeIdentifier = $typeIdentifier;
        $this->type           = $type;
    }

    public function getTypeIdentifier()
    {
        if (!$this->typeIdentifier) {
            throw new RuntimeException('Cannot get get identifier for variable as one does not exist');
        }
        
        return $this->typeIdentifier->getIdentifier();
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    public function toString()
    {
        return sprintf('%s (%s)', parent::toString(), $this->typeIdentifier->toString());
    }
}
