<?php

namespace Ehimen\Jaslang\FuncDef;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;

interface FuncDef
{
    /**
     * @return ArgDef[]
     */
    public function getArgDefs();
    
    public function invoke(ArgList $args, EvaluationContext $context);
    
    // TODO: return values!
}
