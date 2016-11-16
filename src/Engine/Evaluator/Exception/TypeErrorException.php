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
    
    public static function evaluationResultTypeMismatch($expected, Value $value)
    {
        // TODO: need to report actual type received.
        return new static(sprintf(
            'Expected evaluation to result in type %s but got "%s"',
            $expected,
            $value->toString()
        ));
    }
}
