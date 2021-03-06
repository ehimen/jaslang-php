<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\Ast\Visitor;
use Ehimen\Jaslang\Engine\Type\ConcreteType;

class Literal implements Expression
{
    private $value;

    /**
     * @var ConcreteType
     */
    private $type;
    
    public function __construct(ConcreteType $type, $value)
    {
        $this->type  = $type;
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

    public function accept(Visitor $visitor)
    {
        $visitor->visitLiteral($this);
    }
}
