<?php

namespace Ehimen\Jaslang\Core\Value;

use Ehimen\Jaslang\Engine\Exception\LogicException;
use Ehimen\Jaslang\Engine\Value\Value;

/**
 * Wrapping value around an array access.
 * 
 * This contains the evaluated values that were listed in an array access list operator (i.e. [ and ]).
 * 
 * This isn't strictly just array access, but also array literals, e.g. [1, 2, 3].
 * TODO: not actually implemented this claim!
 */
class ArrayAccess implements Value
{
    private $values = [];
    
    public function __construct(Arr $array, $index)
    {
        $this->array = $array;
    }

    /**
     * @return int
     */
    public function getInitialArraySize()
    {
        if (!$this->isArrayInitialisation()) {
            throw new LogicException('Cannot request initial array size from array access that is not initialisation');
        }
        
        if ($this->values[0] instanceof Num) {
            return $this->values[0]->getValue();
        }
        
        return 0;
    }

    private function addArgument(Value $argument)
    {
        $this->values[] = $argument;
    }

    public function toString()
    {
        return sprintf(
            'array: %s',
            implode(
                ',',
                array_map(
                    function (Value $value) {
                        return $value->toString();
                    },
                    $this->values
                )
            )
        );
    }
}
