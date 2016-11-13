<?php

namespace Ehimen\Jaslang\Engine\Type;

/**
 * A Jaslang native type.
 *
 * This interface describes only "virtual" types, who only exist
 * to group other types.
 *
 * An example of a virtual type would be "Any", from which all other
 * types should extend. This allows signatures to accept any argument.
 *
 * @see ConcreteType for types that can be constructed.
 */
interface Type
{
    /**
     * Returns true if this type is satisfied by other.
     * 
     * In terms of type grouping, this should return true
     * if this is in group $other, but not if 
     * $other is in group this.
     *
     * E.g.
     *  $any->isA($string) false
     *  $string->isA($any) true
     * 
     * Having this as a method allows types to have
     * complex relations with one another. This provides
     * flexibility of custom type systems.
     * 
     * @param Type $other
     *
     * @return bool
     */
    public function isA(Type $other);
}
