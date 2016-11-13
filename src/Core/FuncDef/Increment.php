<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Core\Value;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
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

    public function invoke(ArgList $args, EvaluationContext $context)
    {
        /** @var Variable $var */
        $var = $args->get(0);
        
        /** @var Value\Num $value */
        $value = $context->getVariableOfTypeOrThrow($var->getIdentifier(), new Type\Num());
        
        $context->getSymbolTable()->set($var->getIdentifier(), new Value\Num($value->getValue() + 1));
        
        return $value;
    }
}
