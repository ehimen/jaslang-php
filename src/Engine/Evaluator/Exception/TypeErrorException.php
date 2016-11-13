<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Exception;

use Ehimen\Jaslang\Engine\Value\Value;

/**
 * Results from type mismatches.
 * 
 * E.g. attempt to boolean negate a string
 */
class TypeErrorException extends RuntimeException
{
    public static function valueTypeMismatch($expected, $actual, Value $value)
    {
        return new static(sprintf(
            'Expected value of type %s but got "%s" [type: %s]',
            $expected,
            $value->toString(),
            $actual
        ));
    }
}
