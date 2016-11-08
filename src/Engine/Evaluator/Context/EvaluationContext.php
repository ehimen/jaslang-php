<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

use Ehimen\Jaslang\Engine\Type\TypeRepository;

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
}
