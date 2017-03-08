<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Exception\RuntimeException;

class Collection implements Argument
{
    /**
     * @var Argument[]
     */
    private $args = [];

    public function addArgument(Argument $argument)
    {
        $this->args[] = $argument;
    }
    
    public function toString()
    {
        return __CLASS__;
    }

    /**
     * @return Expression[]
     */
    public function getExpressions()
    {
        foreach ($this->args as $arg) {
            if (!($arg instanceof Expression)) {
                throw new RuntimeException('Request for expressions from collection, but it contained a non-expression');
            }
        }
        
        return $this->args;
    }
}
