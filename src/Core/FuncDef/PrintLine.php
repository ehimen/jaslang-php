<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\Value\Printable;

/**
 * Prints content, followed by a line ending.
 */
class PrintLine extends PrintDef
{
    public function write(Evaluator $evaluator, Printable $printable)
    {
        $return = parent::write($evaluator, $printable);

        $evaluator->getContext()->getOutputBuffer()->write(PHP_EOL);
        
        return $return;
    }


    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        $return = parent::invoke($args, $context, $evaluator);
        
        $context->getOutputBuffer()->write(PHP_EOL);
        
        return $return;
    }
}
