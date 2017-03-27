<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Type\Arr;
use Ehimen\Jaslang\Core\Value\ArrayInitialisation;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\Exception\LogicException;
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

    public function typedArray(Evaluator $evaluator, Variable $variable, ArrayInitialisation $initialisation)
    {
        return new TypedVariable(
            $variable->getIdentifier(),
            $initialisation->getAsType()
        );
    }

    public function typed(Evaluator $evaluator, Variable $variable, TypeIdentifier $type)
    {
        return new TypedVariable(
            $variable->getIdentifier(),
            $evaluator->getContext()->getSymbolTable()->getType($type->getIdentifier()),
            $type
        );
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        /** @var Variable $var */
        $var = $args->get(0);

        $type = $args->get(1);
        
        $typeIdentifier = null;

        if ($type instanceof ArrayInitialisation) {
            $type = $type->getAsType();
        } elseif ($type instanceof TypeIdentifier) {
            $typeIdentifier = $type;
            $type = $context->getTypeRepository()->getTypeByName($typeIdentifier->getIdentifier());
        } else {
            throw InvalidArgumentException::invalidArgument(1, 'type', $args->get(1));
        }
        
        return new TypedVariable($var->getIdentifier(), $type, $typeIdentifier);
    }
}
