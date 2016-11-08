<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Type\Type;

class Parameter
{
    const TYPE_TYPE  = 'type';
    const TYPE_VAR   = 'val';
    const TYPE_VALUE = 'value';

    /**
     * @var bool
     */
    private $optional;

    /**
     * @var string
     */
    private $type;

    /**
     * @var TypeIdentifier
     */
    private $expectedType;

    private function __construct($type, Type $expectedType = null, $optional = false)
    {
        $this->type         = $type;
        $this->optional     = $optional;
        $this->expectedType = $expectedType;
    }

    /**
     * Denotes a parameter which expects to receive a value of a particular type.
     */
    public static function value(Type $type, $optional = false)
    {
        return new static(static::TYPE_VALUE, $type, $optional);
    }

    /**
     * Denotes a parameter which expects to receive a type identifier.
     */
    public static function type($optional = false)
    {
        return new static(static::TYPE_TYPE, null, $optional);
    }

    /**
     * Denotes a parameter which expects to receive a variable of a particular type.
     */
    public static function variable($optional = false)
    {
        return new static(static::TYPE_VAR, null, $optional);
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
    
    public function getExpectedType()
    {
        return $this->expectedType;
    }

    public function isOptional()
    {
        return $this->optional;
    }
}
