<?php

namespace Ehimen\JaslangTestResources\CustomType;

use Ehimen\Jaslang\Type\Core\Any;
use Ehimen\Jaslang\Type\Type;

class ParentType implements Type
{
    public function getParent()
    {
        return new Any(); 
    }
}