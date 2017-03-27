<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Core\Value;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Variable;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;

class Increment implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::variable(),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        /** @var Variable $var */
        $var = $args->get(0);
        
        /** @var Value\Num $value */
    }

    public function increment(Evaluator $evaluator, Variable $variable)
    {
        $context = $evaluator->getContext();
        
        /** @var Value\Num $value */
        $value = $context->getVariableOfTypeOrThrow($variable->getIdentifier(), new Type\Num());

        $context->getSymbolTable()->set($variable->getIdentifier(), new Value\Num($value->getValue() + 1));

        return $value;
    }
}
