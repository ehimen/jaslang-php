<?php

namespace Ehimen\Jaslang\Core\Type;

use Ehimen\Jaslang\Engine\Type\Type;

/**
 * All Jaslang types derive from this.
 */
class BaseType implements Type
{
    public function isA(Type $other)
    {
        if ($other instanceof Any) {
            return true;
        }
        
        return ($other instanceof static);
    }

    public function matchesEverything()
    {
        return false;
    }
}
