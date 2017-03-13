<?php

namespace Ehimen\Jaslang\Core\Type;

use Ehimen\Jaslang\Engine\Lexer\Token;
use Ehimen\Jaslang\Engine\Type\ConcreteType;
use Ehimen\Jaslang\Engine\Type\Type;
use Ehimen\Jaslang\Engine\Value\Value;
use Ehimen\Jaslang\Core\Value\Arr as ArrayValue;

class Arr extends BaseType implements ConcreteType
{
    /**
     * @var Type
     */
    private $elementType;
    
    private $size;

    public function __construct(ConcreteType $elementType, $size = 0)
    {
        $this->elementType = $elementType;
        $this->size = $size;
    }
    
    public function createValue($value)
    {
        // TODO: Implement createValue() method.
    }

    public function createEmptyValue()
    {
        $value = new ArrayValue($this->size, $this->elementType);
        
        for ($i = 0; $i < $this->size; $i++) {
            $value->set($i, $this->elementType->createEmptyValue());
        }
        
        return $value;
    }

    public function appliesToValue(Value $value)
    {
        return ($value instanceof ArrayValue);
    }

    public function appliesToToken(Token $token)
    {
        return false;
    }

    public function getStringForValue($value)
    {
        return '';
    }

    public function getLiteralPattern()
    {
        // TODO: Implement getLiteralPattern() method.
    }
}
