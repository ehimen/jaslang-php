<?php

namespace Ehimen\Jaslang\Type;

use Ehimen\Jaslang\Exception\OutOfBoundsException;
use Ehimen\Jaslang\Type\ConcreteType;
use Ehimen\Jaslang\Type\Type;
use Ehimen\Jaslang\Value\Value;

/**
 * Manages types for a Jaslang runtime.
 */
class TypeRepository
{
    private $types = [];
    
    public function registerType($name, Type $type)
    {
        $this->types[$name] = $type;
    }

    /**
     * @return ConcreteType[]
     */
    public function getConcreteTypes()
    {
        return array_filter(
            $this->types,
            function ($type) {
                return ($type instanceof ConcreteType);
            }
        );
    }

    public function getConcreteTypeLiteralPatterns()
    {
        return array_filter(
            array_map(
                function ($type) {
                    if (!($type instanceof ConcreteType)) {
                        return null;
                    }
                    
                    return $type->getLiteralPattern();
                },
                $this->types
            )
        );
    }

    public function getTypeName(Type $type)
    {
        foreach ($this->types as $name => $candidate) {
            if (get_class($candidate) === get_class($type)) {
                return $name;
            }
        }
        
        throw new OutOfBoundsException('Type not registered with type repository; could not find name.');
    }

    /**
     * @param Value $value
     * @return ConcreteType
     */
    public function getTypeByValue(Value $value)
    {
        foreach ($this->types as $type) {
            if ($type instanceof ConcreteType) {
                if ($type->appliesToValue($value)) {
                    return $type;
                }
            }
        }
        
        throw new OutOfBoundsException(sprintf(
            'Could not find type for value "%s".',
            $value->toString()
        ));
    }

    public function getTypeByName($name)
    {
        if (!isset($this->types[$name])) {
            throw new OutOfBoundsException(sprintf(
                'No type with name "%s" has been registered',
                $name
            ));
        }
        
        return $this->types[$name];
    }
}