<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Core\Type\Num as NumType;
use Ehimen\Jaslang\Core\Value\Num;

class Random implements FuncDef
{
    public function getParameters()
    {
        return [
            Parameter::value(new NumType(), true),
            Parameter::value(new NumType(), true),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        if (!$args->has(0)) {
            $result = rand();
        } elseif (!$args->has(1)) {
            $result = rand($args->get(0));
        } else {
            $result = rand($args->get(0), $args->get(1));
        }
        
        return new Num($result);
    }

    public function unbounded(Evaluator $evaluator)
    {
        return new Num(rand());
    }

    public function bounded(Evaluator $evaluator, Num $lower, Num $upper)
    {
        // TODO: don't support just one param anymore. Okay?
        return new Num(rand($lower->getValue(), $upper->getValue()));
    }
}
