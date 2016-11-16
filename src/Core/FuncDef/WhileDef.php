<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Core\Type;
use Ehimen\Jaslang\Core\Value;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\TypeErrorException;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Routine;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expression;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use phpDocumentor\Reflection\DocBlock\Tags\Param;

class WhileDef implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::expression(),
            Parameter::routine(),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        /** @var Expression $while */
        $while = $args->get(0);
        /** @var Routine $do */
        $do = $args->get(1);
        
        while (true) {
            $while->getExpression()->accept($evaluator);
            
            $result = $evaluator->getResult();
            
            if (!($result instanceof Value\Boolean)) {
                throw TypeErrorException::evaluationResultTypeMismatch('boolean', $result);
            }
            
            if ($result->getValue()) {
                $do->getRoutine()->accept($evaluator);
            } else {
                break;
            }
        }
        
    }
}
