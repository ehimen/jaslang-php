<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypedVariable;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeIdentifier;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Variable;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;

class Let implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::typedVariable()
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        /** @var TypedVariable $variable */
        $variable = $args->get(0);
        
        // TODO: try, catch and throw??
        $type = $context->getTypeRepository()->getTypeByName($variable->getType()->getIdentifier());
        
        $context->getSymbolTable()->set($variable->getIdentifier(), $type->createEmptyValue());
        
        return $variable;
    }
}
