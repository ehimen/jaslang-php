<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Core\Value;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Block;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;

class IfDef implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::value(new Type\Boolean()),
            Parameter::block(),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context)
    {
        /** @var Value\Boolean $do */
        $do = $args->get(0);
        /** @var Block $action */
        $action = $args->get(1);
        
        if ($do->getValue()) {
            $context->evaluateInContext($action->getBlock());
        }
        
        return new Value\Boolean($do);
    }
}
