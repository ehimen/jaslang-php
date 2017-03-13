<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Type\Arr;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeIdentifier;
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
        $firstArg = $args->get(0);
        
        if ($firstArg instanceof TypeIdentifier) {
            // Handle array initialisation, i.e.: type[size].
            $second = $args->get(1);
            $size = ($second instanceof Value\Num) ? $second->getValue() : 0;
            $type = $context->getTypeRepository()->getTypeByName($firstArg->getIdentifier());

            return new Value\ArrayInitialisation($type, $size);
        }
        
        throw new \Exception('TODO: not implemented');
    }
}
