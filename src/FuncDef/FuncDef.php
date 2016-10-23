<?php

namespace Ehimen\Jaslang\FuncDef;

abstract class FuncDef
{
    /**
     * @return ArgDef[]
     */
    abstract public function getArgDefs();
    
    abstract public function invoke(ArgList $args, $context = null);
    
    // TODO: return values!
}