<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

interface ContextStack
{
    /**
     * Gets the current context;
     * 
     * @return EvaluationContext
     */
    public function getContext();
    
    /**
     * Creates a new evaluation context.
     * 
     * @return EvaluationContext
     */
    public function createContext();

    /**
     * Pops a context, meaning that further createContext() calls will be based off the previous context.
     */
    public function popContext();

    public function reset();
}
