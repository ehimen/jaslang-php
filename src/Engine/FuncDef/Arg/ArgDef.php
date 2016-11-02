<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Type\Type;

class ArgDef
{
    /**
     * @var bool
     */
    private $optional;

    /**
     * @var Type
     */
    private $type;

    public function __construct(Type $type, $optional = false)
    {
        $this->type     = $type;
        $this->optional = $optional;
    }
    
    public function getType()
    {
        return $this->type;
    }

    public function isOptional()
    {
        return $this->optional;
    }
}
