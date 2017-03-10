<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expression;
use Ehimen\Jaslang\Engine\FuncDef\VariableArgFuncDef;
use Ehimen\Jaslang\Core\Value;

class ArrayAccess implements VariableArgFuncDef
{
    public function getParameters()
    {
        return [
            Parameter::type(),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        $values = [];
        
        for ($i = 0; $i < $args->count(); $i++) {
            $arg = $args->get($i);
            
            if (!($arg instanceof \Ehimen\Jaslang\Engine\Value\Value)) {
                throw new RuntimeException(sprintf('Illegal array access: %s', $arg->toString()));
            }
            
            $values[] = $arg;
        }
        
        return new Value\ArrayAccess($values);
    }
}
