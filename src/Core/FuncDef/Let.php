<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\TypeErrorException;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expression;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypedVariable;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeIdentifier;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Variable;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;

class Let implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::expression()
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        /** @var Expression $expression */
        $expression = $args->get(0);
        
        $expression->getExpression()->accept($evaluator);
        
        $result = $evaluator->getResult();
        
        if (!($result instanceof TypedVariable)) {
            throw new TypeErrorException('Excepted variable with type');
        }
        
        // TODO: try, catch and throw??
        $type = $context->getTypeRepository()->getTypeByName($result->getType()->getIdentifier());
        
        $context->getSymbolTable()->set($result->getIdentifier(), $type->createEmptyValue());
        
        return $result;
    }
}
