<?php

namespace Ehimen\Jaslang\Engine\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Argument;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;

interface FuncDef
{
    /**
     * Gets the definition of parameters that this function expects.
     *
     * @return Parameter[]
     */
    public function getParameters();

    /**
     * @param ArgList           $args
     * @param EvaluationContext $context
     * @param Evaluator         $evaluator
     *
     * @return Argument
     */
    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator);
    
    // TODO: return values!
}
