<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

use Ehimen\Jaslang\Engine\FuncDef\OperatorSignature;

/**
 * A node in the AST that can take precedence over others.
 *
 * This facilitates operators taking precendence over previously-occuring operators.
 *
 * We shuffle the AST as it's being built if a later node takes precendence over another.
 */
interface PrecedenceRespectingNode extends ParentNode
{
    /**
     * @return OperatorSignature
     */
    public function getSignature();
}
