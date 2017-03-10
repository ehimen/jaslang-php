<?php

namespace Ehimen\Jaslang\Engine\Ast;

use Ehimen\Jaslang\Engine\Ast\Node;

interface Visitor
{
    public function visitBlock(Node\Block $node);

    public function visitContainer(Node\Container $node);

    public function visitFunctionCall(Node\FunctionCall $node);

    public function visitIdentifier(Node\Identifier $node);

    public function visitLiteral(Node\Literal $node);

    public function visitOperator(Node\Operator $node);

    public function visitRoot(Node\Root $node);

    public function visitStatement(Node\Statement $node);

    public function visitTuple(Node\Tuple $node);
}
