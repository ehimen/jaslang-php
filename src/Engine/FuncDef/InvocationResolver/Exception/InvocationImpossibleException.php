<?php

namespace Ehimen\Jaslang\Engine\FuncDef\InvocationResolver\Exception;

use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;

class InvocationImpossibleException extends RuntimeException
{
    public static function fromFuncDef(FuncDef $funcDef)
    {
        return new static(sprintf('Cannot invoke funcdef %s', get_class($funcDef)));
    }
}
