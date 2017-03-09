<?php

namespace Ehimen\Jaslang\Core\Value;

use Ehimen\Jaslang\Engine\Value\Value;

/**
 * Wrapper around a value to indicate it was explicitly returned.
 */
class ExplicitReturn implements Value
{
    private $wrapped;
    
    public function __construct(Value $value)
    {
        $this->wrapped = $value;
    }
    
    public function toString()
    {
        return $this->wrapped->toString();
    }

    public function getWrapped()
    {
        return $this->wrapped;
    }
}
