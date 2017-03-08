<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

interface ContextFactory
{
    /**
     * Creates a new evaluation context.
     * 
     * @return EvaluationContext
     */
    public function createContext();

    /**
     * Extends an evaluation context, returning a new context based on the provided.
     *
     * @return EvaluationContext
     */
    public function extendContext(EvaluationContext $base);
}
