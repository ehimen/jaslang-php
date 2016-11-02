<?php

namespace Ehimen\JaslangTestResources\CustomType;

use Ehimen\Jaslang\Engine\Type\Core\Any;
use Ehimen\Jaslang\Engine\Type\Type;

class ParentType implements Type
{
    public function getParent()
    {
        return new Any(); 
    }
}