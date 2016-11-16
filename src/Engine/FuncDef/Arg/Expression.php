<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Ast;

class Expression implements Argument
{
    /**
     * @var Ast\Node\Expression
     */
    private $expression;

    public function __construct(Ast\Node\Expression $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return $this->expression->debug();
    }

    /**
     * @return Ast\Node\Expression
     */
    public function getExpression()
    {
        return $this->expression;
    }
}
