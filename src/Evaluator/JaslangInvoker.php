<?php

namespace Ehimen\Jaslang\Evaluator;

use Ehimen\Jaslang\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Operator\Operator;

/**
 * TODO: don't encourage inheritance, composition via ContextFactory?
 */
class JaslangInvoker implements Invoker
{
    public function invokeFuncDef(FuncDef $function, ArgList $args)
    {
        $this->validateArgs($function->getArgDefs(), $args);
        
        return $function->invoke($args, $this->getContext($function, $args));
        
        // TODO: return type.
    }

    public function invokeOperator(Operator $operator, ArgList $args)
    {
        return $operator->invoke($args);
    }

    protected function getContext(FuncDef $function, ArgList $args)
    {
        return [];
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