<?php

namespace Ehimen\Jaslang\FuncDef\Core;

use Ehimen\Jaslang\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Value\Num;

class Random extends FuncDef
{
    public function getArgDefs()
    {
        return [
            new ArgDef(ArgDef::NUMBER, true),
            new ArgDef(ArgDef::NUMBER, true),
        ];
    }

    public function invoke(ArgList $args, $context = null)
    {
        if ($min = $args->getNumber(0, true)) {
            if (!($max = $args->getNumber(1))) {
                throw new InvalidArgumentException(1, ArgDef::NUMBER, null);
            }
            
            return new Num(rand($min->getValue(), $max->getValue()));
        }
        
        return new Num(rand());
    }
}