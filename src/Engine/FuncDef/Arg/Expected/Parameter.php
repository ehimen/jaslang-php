<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg\Expected;

use Ehimen\Jaslang\Engine\Type\Type;

/**
 * The description of which parameter(s) a function/operator expects.
 */
class Parameter
{
    const TYPE_TYPE       = 'type';
    const TYPE_VAR        = 'var';
    const TYPE_VALUE      = 'value';
    const TYPE_ROUTINE    = 'routine';
    const TYPE_EXPRESSION = 'expression';
    const TYPE_TYPED_VAR  = 'typed-var';
    const TYPE_COLLECTION = 'collection';
    const TYPE_ANY        = 'anything';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     * 
     * For collection type, one of the other types.
     */
    private $parameterType;

    protected function __construct($type)
    {
        $this->type = $type;
    }
    
    /**
     * Denotes a parameter which expects to receive a value of a particular type.
     * 
     * TODO: move to TypedParameter
     */
    public static function value(Type $type)
    {
        return new TypedParameter(static::TYPE_VALUE, $type);
    }

    /**
     * Denotes a parameter which expects to receive a type identifier.
     */
    public static function type()
    {
        return new static(static::TYPE_TYPE);
    }

    /**
     * Denotes a parameter which expects to receive a variable of a particular type.
     */
    public static function variable()
    {
        return new static(static::TYPE_VAR);
    }

    public static function collection($parameterType)
    {
        $collection = new static(static::TYPE_COLLECTION);
        
        $collection->parameterType = $parameterType;
        
        return $collection;
    }

    /**
     * Denotes a parameter which expects to receive executable statement(s).
     */
    public static function routine()
    {
        return new static(static::TYPE_ROUTINE);
    }

    public static function expression()
    {
        return new static(static::TYPE_EXPRESSION);
    }
    
    public static function any()
    {
        return new static(static::TYPE_ANY);
    }

    public function isVariable()
    {
        return (static::TYPE_VAR === $this->type);
    }

    public function isType()
    {
        return (static::TYPE_TYPE === $this->type);
    }

    public function isValue()
    {
        return (static::TYPE_VALUE === $this->type);
    }

    public function isRoutine()
    {
        return (static::TYPE_ROUTINE === $this->type);
    }

    public function isExpression()
    {
        return (static::TYPE_EXPRESSION === $this->type);
    }

    public function isCollection()
    {
        return (static::TYPE_COLLECTION === $this->type);
    }
    
    public function isAny()
    {
        return (static::TYPE_ANY === $this->type);
    }

    /**
     * For collection type parameters, what is the type expected in that collection?
     */
    public function getParameterType()
    {
        return $this->parameterType;
    }
}
