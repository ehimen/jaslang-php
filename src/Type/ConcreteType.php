<?php

namespace Ehimen\Jaslang\Type;

use Ehimen\Jaslang\Value\Value;

/**
 * A type which have can have concrete values.
 */
interface ConcreteType extends Type
{
    /**
     * @param $value
     * @return Value
     */
    public function createValue($value);

    public function appliesToValue(Value $value);

    /**
     * @param array $token
     * @return bool
     */
    public function appliesToToken(array $token);

    /**
     * Describes the value as would be interpreted by the type.
     *
     * Used for debugging/error reporting.
     *
     * @return string
     */
    public function getStringForValue($value);

    /**
     * Returns a regex pattern which matches a literal value of this type.
     * 
     * For example, a type that expects all lower case characters for its values, return "[a-z]".
     * This is used by the lexer to allow powerful customisation of native types in a language.
     * 
     * If this type does not have special literal syntax, just return null.
     * For example, if your type worked on a particular string format, we don't
     * need to provide a pattern as the lexer already captures strings.
     * Instead, we'd just need to implement appliesToToken() to ensure
     * that the string(s) were considered a literal of this type, and not
     * a string literal.
     * 
     * @return string|null
     */
    public function getLiteralPattern();
}