<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;

/**
 * Prints content, followed by a line ending.
 */
class PrintLine extends PrintDef
{
    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        $return = parent::invoke($args, $context, $evaluator);
        
        $context->getOutputBuffer()->write(PHP_EOL);
        
        return $return;
    }
}
