<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypedVariable;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeIdentifier;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Variable;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;

/**
 * An operator that binds a variable identifier with a type.
 * 
 * let n : number = 13
 *     ^________^
 */
class VariableWithType implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::variable(),
            Parameter::type(),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        /** @var Variable $var */
        $var = $args->get(0);
        /** @var TypeIdentifier $type */
        $type = $args->get(1);
        
        return new TypedVariable($var->getIdentifier(), $type);
    }
}
