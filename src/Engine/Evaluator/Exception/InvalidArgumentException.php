<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Exception;

use Ehimen\Jaslang\Engine\FuncDef\Arg\Argument;
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

    public static function invalidArgument($index, $expectedType, Argument $actual = null)
    {
        $exception = new static(sprintf(
            'Invalid argument at position %d. Expected "%s", got %s',
            $index,
            $expectedType,
            $actual ? $actual->toString() : '[empty]'
        ));

        $exception->expectedType = $expectedType;
        $exception->actual       = $actual;
        
        return $exception;
    }

    public static function unexpectedArgument($expectedCount)
    {
        return new static(sprintf(
            'Too many arguments. Expected a total of %d',
            $expectedCount
        ));
    }
}
