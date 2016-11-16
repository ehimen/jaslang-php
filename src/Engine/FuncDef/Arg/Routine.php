<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Ast;

/**
 * An argument to a function which is a block of code.
 * 
 * This allows for implementation of control structures
 * (e.g. if, while) as functions against the Jaslang engine.
 */
class Routine implements Argument
{
    /**
     * @var Ast\Node\Routine
     */
    private $routine;

    public function __construct(Ast\Node\Routine $routine)
    {
        $this->routine = $routine;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return $this->routine->debug();
    }

    /**
     * @return Ast\Node\Routine
     */
    public function getRoutine()
    {
        return $this->routine;
    }
}
