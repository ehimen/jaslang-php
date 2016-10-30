<?php

namespace Ehimen\Jaslang\FuncDef;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\FuncDef\Arg\ArgDef;
use Ehimen\Jaslang\FuncDef\Arg\ArgList;

interface FuncDef
{
    /**
     * @return ArgDef[]
     */
    public function getArgDefs();
    
    public function invoke(ArgList $args, EvaluationContext $context);
    
    // TODO: return values!
}
