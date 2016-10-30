<?php

namespace Ehimen\Jaslang\Ast;

use Ehimen\Jaslang\Type\ConcreteType;
use Ehimen\Jaslang\Type\Type;

class Literal implements Node
{
    private $value;

    /**
     * @var ConcreteType
     */
    private $type;
    
    public function __construct(ConcreteType $type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function debug()
    {
        return $this->type->getStringForValue($this->value);
    }

    public function getType()
    {
        return $this->type;
    }
}