<?php

namespace Ehimen\Jaslang\Ast;

/**
 * A base node for all ASTs.
 *
 * Note this is not a normal parent node as we should only be adding statements
 * to this node.
 */
class Root implements Node
{
    /**
     * @var Statement[]
     */
    private $statements;

    public function __construct(array $statements = [])
    {
        $this->statements = [];
    }

    public function debug()
    {
        return 'TODO';
    }

    public function getStatements()
    {
        return $this->statements;
    }

    /**
     * @return Statement
     */
    public function getFirstStatement()
    {
        // TODO: throw.
        return reset($this->statements);
    }

    public function getLastStatement()
    {
        return end($this->statements);
    }
}
