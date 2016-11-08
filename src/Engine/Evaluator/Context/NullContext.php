<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

use Ehimen\Jaslang\Engine\Type\TypeRepository;

/**
 * A null context to satisfy calls whilst context is being fleshed out.
 */
class NullContext implements EvaluationContext
{
    public function getSymbolTable()
    {
        return new SymbolTable();
    }

    public function getTypeRepository()
    {
        return new TypeRepository();
    }
}
