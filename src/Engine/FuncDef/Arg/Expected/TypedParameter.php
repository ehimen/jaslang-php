<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg\Expected;

use Ehimen\Jaslang\Engine\Type\Type;

/**
 * "Typed" in this context means a userland type.
 * 
 * This differs from the other types of parameters we can
 * expect, which are more abstract concepts such as blocks of code,
 * variable identifiers.
 * 
 * E.g. we expect the value to a Jaslang function to be a string, or number, etc.
 */
class TypedParameter extends Parameter
{
    /**
     * @var Type
     */
    private $expectedType;

    protected function __construct($type, Type $expectedType)
    {
        parent::__construct($type);
        $this->expectedType = $expectedType;
    }
    
    public function getExpectedType()
    {
        return $this->expectedType;
    }
}
