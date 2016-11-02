<?php

namespace Ehimen\Jaslang\Engine\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgDef;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;

interface FuncDef
{
    /**
     * @return ArgDef[]
     */
    public function getArgDefs();
    
    public function invoke(ArgList $args, EvaluationContext $context);
    
    // TODO: return values!
}
