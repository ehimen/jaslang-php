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
    }

    public function let(Evaluator $evaluator, Expression $expression)
    {
        $result = $evaluator->evaluateInIsolation($expression->getExpression());

        if (!($result instanceof TypedVariable)) {
            throw new TypeErrorException('Excepted variable with type');
        }

        $evaluator->getContext()->getSymbolTable()->set($result->getIdentifier(), $result->getType()->createEmptyValue());

        return $result;
    }
}
