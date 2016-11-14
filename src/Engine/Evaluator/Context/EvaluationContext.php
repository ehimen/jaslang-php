<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

use Ehimen\Jaslang\Engine\Ast\Node;
use Ehimen\Jaslang\Engine\Evaluator\Exception\TypeErrorException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedSymbolException;
use Ehimen\Jaslang\Engine\Type\Type;
use Ehimen\Jaslang\Engine\Type\TypeRepository;
use Ehimen\Jaslang\Engine\Value\Value;

/**
 * Some context of evaluation.
 *
 * This will hold information when invoking functions/callables,
 * such as variables in scope, the output buffer etc.
 */
interface EvaluationContext
{
    /**
     * @return SymbolTable
     */
    public function getSymbolTable();

    /**
     * @return TypeRepository
     */
    public function getTypeRepository();

    /**
     * @param string $name
     * @param Type $type
     *
     * @throws UndefinedSymbolException
     * @throws TypeErrorException
     * 
     * @return Value
     */
    public function getVariableOfTypeOrThrow($name, Type $type);

    /**
     * Evaluates $node in this context.
     * 
     * @param Node $node
     */
    public function evaluateInContext(Node $node);
}
