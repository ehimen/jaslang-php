<?php

namespace Ehimen\JaslangTestResources;

use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Value\Str;

/**
 * Returns the string "foo".
 */
class FooFuncDef extends FuncDef 
{
    public function getArgDefs()
    {
        return [];
    }

    public function invoke(ArgList $args, $context = null)
    {
        return new Str("foo");
    }
}