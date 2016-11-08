<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeIdentifier;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Variable;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;

class Let implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::type(),
            Parameter::variable()
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context)
    {
        /** @var TypeIdentifier $type */
        $type = $args->get(0);
        /** @var Variable $var */
        $var  = $args->get(1);
        
        // TODO: try, catch and throw??
        $type = $context->getTypeRepository()->getTypeByName($type->getIdentifier());
        
        $context->getSymbolTable()->set($var->getIdentifier(), $type->createEmptyValue());
        
        return $var;
    }
}