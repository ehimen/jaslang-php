<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\Value\Printable;

/**
 * Prints to the output buffer.
 */
class PrintDef implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::value(new Type\Any()),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        /** @var Printable $val */
        $val = $args->get(0);
        
        $context->getOutputBuffer()->write($val->printValue());
    }
}
