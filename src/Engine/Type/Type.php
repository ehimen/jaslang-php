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
     * @return static|null
     */
    public function getParent();
}