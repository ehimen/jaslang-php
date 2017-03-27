<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Type\Any;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypedVariable;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Variable;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\Type\ConcreteType;
use Ehimen\Jaslang\Engine\Value\Value;

class Assign implements FuncDef
{
    /**
     * When assignment attempt from value to an incompatible variable.
     * 
     * @param string $expectedTypeName
     * @param Value  $value
     *
     * @return RuntimeException
     */
    public static function typeMismatch($expectedTypeName, Value $value)
    {
        return new RuntimeException(sprintf(
            'Assignment expected value of type %s, but got "%s"',
            $expectedTypeName,
            $value->toString()
        ));
    }
    
    public function getParameters()
    {
        return [
            Parameter::variable(),
            Parameter::value(new Any()),
        ];
    }

    public function assign(Evaluator $evaluator, Variable $variable, Value $value)
    {
        $symbolTable = $evaluator->getContext()->getSymbolTable();
        
        $type     = $symbolTable->getValueType($variable->getIdentifier());
        $typeName = $symbolTable->getValueTypeName($variable->getIdentifier());
        
        if (!($type instanceof ConcreteType)) {
            throw new RuntimeException(sprintf('Illegal assignment. Expecting value of type "%s"', $typeName));
        }
        
        if (!$type->appliesToValue($value)) {
            throw static::typeMismatch($typeName, $value);
        }
        
        $symbolTable->set($variable->getIdentifier(), $value);
        
        return $value;
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        /** @var Variable $variable */
        $variable = $args->get(0);
        /** @var Value $value */
        $value = $args->get(1);
        
        $existing = $context->getSymbolTable()->get($variable->getIdentifier());
        
        $type = $context->getTypeRepository()->getTypeByValue($existing);
        
        if (!$type->appliesToValue($value)) {
            throw static::typeMismatch($context->getTypeRepository()->getTypeName($type), $value);
        }
        
        $context->getSymbolTable()->set($variable->getIdentifier(), $value);
        
        return $value;
    }

}
