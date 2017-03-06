<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

class Collection implements Argument
{
    /**
     * @var Argument[]
     */
    private $args;

    public function addArgument(Argument $argument)
    {
        $this->args[] = $argument;
    }
    
    public function toString()
    {
        return __CLASS__;
    }
}
