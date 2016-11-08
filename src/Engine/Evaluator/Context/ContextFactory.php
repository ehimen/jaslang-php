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
}