<?php

namespace Ehimen\Jaslang\Value\Core;

use Ehimen\Jaslang\Value\Native;

class Str extends Native
{
    public function castToString()
    {
        return $this;
    }
}