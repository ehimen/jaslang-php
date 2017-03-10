<?php

namespace Ehimen\Jaslang\Core\Type;

class Any extends BaseType
{
    public function matchesEverything()
    {
        return true;
    }
}
