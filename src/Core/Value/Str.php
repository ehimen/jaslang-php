<?php

namespace Ehimen\Jaslang\Core\Value;

use Ehimen\Jaslang\Engine\Value\Native;

class Str extends Native
{
    public function castToString()
    {
        return $this;
    }
}
