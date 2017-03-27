<?php

namespace Ehimen\Jaslang\Engine\Evaluator\SymbolTable;

use Ehimen\Jaslang\Engine\Exception\LogicException;
use Ehimen\Jaslang\Engine\Type\Type;
use Ehimen\Jaslang\Engine\Value\CallableValue;
use Ehimen\Jaslang\Engine\Value\Value;

class Entry
{
    private static $addrGen = 0;
    
    private $addr;

    private $identifier;

    private $value;
    
    private $typeAddr;
    
    private function __construct($addr, $identifier, Value $value, $typeAddr = null)
    {
        $this->addr = $addr;
        $this->identifier = $identifier;
        $this->value = $value;
        $this->typeAddr = $typeAddr;
    }

    public function getAddr()
    {
        return $this->addr;
    }

    public static function create($identifier, Value $value, $typeAddr = null)
    {
        return new static(static::addr(), $identifier, $value, $typeAddr);
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function get()
    {
        return $this->value;
    }

    public function isCallable()
    {
        return ($this->value instanceof CallableValue);
    }

    public function isType()
    {
        return ($this->value instanceof Type);
    }

    /**
     * Gets the contained value if it is a type.
     * 
     * Note this does not return the type of a value. @see getValueTypeAddr
     * 
     * @return Type
     */
    public function getType()
    {
        if (!$this->isType()) {
            throw new LogicException('Entry is not type');
        }
        
        return $this->value;
    }

    /**
     * Gets the address of the type of the value.
     * 
     * Note this is distinct getType(), which only returns if the contained value is itself a type.
     * 
     * @return Type|null
     */
    public function getValueTypeAddr()
    {
        return $this->typeAddr;
    }

    /**
     * @return CallableValue
     */
    public function getCallable()
    {
        if (!$this->isCallable()) {
            throw new LogicException('Entry is not callable');
        }
        
        return $this->value;
    }

    /**
     * Does the value in this entry have a type?
     * 
     * @return bool
     */
    public function hasType()
    {
        return isset($this->typeAddr);
    }

    private static function addr()
    {
        return static::$addrGen++;
    }
}
