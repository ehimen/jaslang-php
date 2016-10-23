<?php

namespace Ehimen\Jaslang\Evaluator;

use Ehimen\Jaslang\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;

/**
 * TODO: don't encourage inheritance, composition via ContextFactory?
 */
class SimpleInvoker implements Invoker
{
    public function invoke(FuncDef $function, ArgList $args)
    {
        $this->validateArgs($function, $args);
        
        return $function->invoke($args, $this->getContext($function, $args));
    }

    protected function getContext(FuncDef $function, ArgList $args)
    {
        return [];
    }

    private function validateArgs(FuncDef $function, ArgList $args)
    {
        // TODO: validate not too many!
        foreach ($function->getArgDefs() as $i => $def) {
            if (!$def->isSatisfiedBy($args->get($i))) {
                throw new InvalidArgumentException($i, $def->getType(), $args->get($index));
            }
        }
    }
}