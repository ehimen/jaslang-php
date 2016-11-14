<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Ast;

/**
 * An argument to a function which is a block of code.
 * 
 * This allows for implementation of control structures
 * (e.g. if, while) as functions against the Jaslang engine.
 */
class Block implements Argument
{
    /**
     * @var Ast\Block
     */
    private $block;

    public function __construct(Ast\Block $block)
    {
        $this->block = $block;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return $this->block->debug();
    }

    /**
     * @return Ast\Block
     */
    public function getBlock()
    {
        return $this->block;
    }
}
