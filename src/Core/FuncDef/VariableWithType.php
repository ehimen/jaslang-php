<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Type\Any;
use Ehimen\Jaslang\Core\Type\Arr;
use Ehimen\Jaslang\Core\Value\ArrayAccess;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
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
        /** @var TypeIdentifier $typeIdentifier */
        $typeIdentifier = $args->get(1);
        $type           = $context->getTypeRepository()->getTypeByName($typeIdentifier->getIdentifier());
        
        if ($args->has(2)) {
            $value = $args->get(2);
            
            if (!($value instanceof ArrayAccess) || !$value->isArrayInitialisation()) {
                throw new RuntimeException(sprintf(
                    'Illegal type modifier for variable "%s": %s',
                    $var->getIdentifier(),
                    $value->toString()
                ));
            }
            
            $type = new Arr($type, $value->getInitialArraySize());
        }
        
        return new TypedVariable($var->getIdentifier(), $typeIdentifier, $type);
    }
}
