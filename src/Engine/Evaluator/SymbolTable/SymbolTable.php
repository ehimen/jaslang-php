<?php

namespace Ehimen\Jaslang\Engine\Evaluator\SymbolTable;

use Ehimen\Jaslang\Engine\Exception\LogicException;
use Ehimen\Jaslang\Engine\Exception\OutOfBoundsException;
use Ehimen\Jaslang\Engine\Type\ConcreteType;
use Ehimen\Jaslang\Engine\Type\Type;
use Ehimen\Jaslang\Engine\Type\TypeRepository;
use Ehimen\Jaslang\Engine\Value\CallableValue;
use Ehimen\Jaslang\Engine\Value\Value;

/**
 * TODO: some repetition here with TypeRepository.
 */
class SymbolTable
{
    /**
     * @var Entry[]
     */
    private $entries = [];

    /**
     * @var self
     */
    private $parent;

    /**
     * @var string[]
     * 
     * A secondary data structure mapping entry address to identifiers.
     */
    private $addrs = [];

    public function __construct(self $parent = null)
    {
        $this->parent = $parent;
    }

    public static function fromTypeRepository(TypeRepository $repository)
    {
        $table = new static();
        
        foreach ($repository->all() as $name => $type) {
            $table->set($name, $type);
        }
        
        return $table;
    }
    
    public function set($identifier, Value $value, Type $type = null)
    {
        if (!$type) {
            try {
                $type = $this->getTypeByValue($value);
            } catch (OutOfBoundsException $e) { }
        }
        
        $typeAddr = null;
        
        foreach ($this->getTypeEntries() as $entry) {
            if ($entry->isType() && (get_class($entry->getType()) === get_class($type))) {
                $typeAddr = $entry->getAddr();
            } 
        }
        
        $entry                      = Entry::create($identifier, $value, $typeAddr);
        $this->entries[$identifier] = $entry;
        $this->addrs[$entry->getAddr()] = $identifier;
    }

    public function has($identifier)
    {
        return isset($this->getEntries()[$identifier]);
    }

    public function hasCallable($identifier)
    {
        return isset($this->getEntries()[$identifier]) && $this->getEntries()[$identifier]->isCallable();
    }

    public function hasType($identifier)
    {
        return isset($this->getEntries()[$identifier]) && $this->getEntries()[$identifier]->isType();
    }

    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new OutOfBoundsException('Unrecognised symbol: ' . $identifier);
        }

        return $this->getEntries()[$identifier]->get();
    }

    /**
     * Gets the type for the value indicated by $identifier.
     */
    public function getValueType($identifier)
    {
        return $this->getValueTypeEntry($identifier)->get();
    }

    /**
     * Gets the type identifier for the value indicated by $identifier.
     */
    public function getValueTypeName($identifier)
    {
        return $this->getValueTypeEntry($identifier)->getIdentifier();
    }

    /**
     * @return CallableValue
     */
    public function getCallable($identifier)
    {
        if (!$this->hasCallable($identifier)) {
            throw new OutOfBoundsException('Unrecognised value: ' . $identifier);
        }
        
        return $this->getEntries()[$identifier]->getCallable();
    }

    /**
     * @param $identifier
     *
     * @return Type
     */
    public function getType($identifier)
    {
        if (!$this->hasType($identifier)) {
            throw new OutOfBoundsException('Unrecognised type: ' . $identifier);
        }

        return $this->getEntries()[$identifier]->getType();
    }

    /**
     * @return Entry[]
     */
    public function getTypeEntries()
    {
        return array_filter(
            $this->getEntries(),
            function (Entry $e) {
                return $e->isType();
            }
        );
    }

    /**
     * @return ConcreteType[]
     */
    public function getConcreteTypes()
    {
        return array_map(
            function (Entry $entry) {
                return $entry->getType();
            },
            array_filter(
                $this->getTypeEntries(),
                function (Entry $entry) {
                    return ($entry->isType() && ($entry->getType() instanceof ConcreteType));
                }
            )
        );
    }

    /**
     * @param Value $value
     *
     * @return Type
     */
    public function getTypeByValue(Value $value)
    {
        foreach ($this->getTypeEntries() as $entry) {
            $type = $entry->getType();
            
            if (($type instanceof ConcreteType) && $type->appliesToValue($value)) {
                return $type;
            }
        }
        
        throw new OutOfBoundsException(sprintf(
            'Could not find type for value "%s".',
            $value->toString()
        ));
    }

    public function getTypeName(Type $type)
    {
        foreach ($this->getTypeEntries() as $entry) {
            if (get_class($entry->getType()) === get_class($type)) {
                return $entry->getIdentifier();
            }
        }

        throw new OutOfBoundsException('Could not find type name. Type not registered.');
    }

    private function getEntries()
    {
        return $this->entries + ($this->parent ? $this->parent->getEntries() : []);
    }

    private function getByAddr($addr)
    {
        if (isset($this->addrs[$addr])) {
            return $this->getEntries()[$this->addrs[$addr]];
        }
        
        if ($this->parent) {
            return $this->parent->getByAddr($addr);
        }
        
        throw new OutOfBoundsException('Unknown addr, ' . $addr);
    }

    /**
     * @param $identifier
     *
     * @return Entry
     */
    private function getValueTypeEntry($identifier)
    {
        if (!$this->has($identifier) || !$this->getEntries()[$identifier]->hasType()) {
            throw new LogicException('Cannot get type for value with identifier: ' . $identifier);
        }

        return $this->getByAddr($this->getEntries()[$identifier]->getValueTypeAddr());
    }
}
