<?php

namespace Ehimen\Jaslang\FuncDef;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;

abstract class FuncDef
{
    /**
     * @return ArgDef[]
     */
    abstract public function getArgDefs();
    
    abstract public function invoke(ArgList $args, EvaluationContext $context);
    
    // TODO: return values!
}
