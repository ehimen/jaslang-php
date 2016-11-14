<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

interface ContextFactory
{
    /**
     * Creates a new evaluation context.
     * 
     * @param \Closure $evaluationFn A function that will evaluate in the created context.
     * 
     * @return EvaluationContext
     */
    public function createContext(\Closure $evaluationFn);
}
