<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Exception;

use Ehimen\Jaslang\Engine\Value\Value;

/**
 * Indicates that an invalid argument was provided to a Jaslang function.
 */
class InvalidArgumentException extends RuntimeException 
{
    /**
     * @var string
     */
    private $expectedType;

    /**
     * @var Value
     */
    private $actual;

    public function __construct($index, $expectedType, Value $actual = null)
    {
        parent::__construct(sprintf(
            'Invalid argument at position %d. Expected "%s", got %s',
            $index,
            $expectedType,
            $actual ? $actual->toString() : '[empty]'
            // TODO: show type!
        ));
        
        $this->expectedType = $expectedType;
        $this->actual       = $actual;
    }
}