<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;

/**
 * Returns input from the input buffer.
 */
class In implements FuncDef
{
    /**
     * @inheritdoc
     */
    public function getParameters()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        // TODO: Implement invoke() method.
    }
}