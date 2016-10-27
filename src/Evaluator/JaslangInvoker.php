<?php

namespace Ehimen\Jaslang\Evaluator;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Operator\Operator;

/**
 * TODO: don't encourage inheritance, composition via ContextFactory?
 */
class JaslangInvoker implements Invoker
{
    public function invokeFuncDef(FuncDef $function, ArgList $args, EvaluationContext $context)
    {
        $this->validateArgs($function->getArgDefs(), $args);
        
        return $function->invoke($args, $context);
        
        // TODO: return type. Really need to validate this. Keep not returning wrapped values!
    }

    public function invokeOperator(Operator $operator, ArgList $args, EvaluationContext $context)
    {
        return $operator->invoke($args, $context);
    }

    private function validateArgs(array $argDefs, ArgList $args)
    {
        // TODO: validate not too many!
        foreach ($argDefs as $i => $def) {
            if (!$def->isSatisfiedBy($args->get($i))) {
                throw new InvalidArgumentException($i, $def->getType(), $args->get($i));
            }
        }
    }
}
