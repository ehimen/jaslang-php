<?php

namespace Ehimen\Jaslang\Operator;

use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\FuncDef\ArgList;

interface Operator
{
    /**
     * @return ArgDef[]
     */
    public function getArgDefs();

    public function invoke(ArgList $operands);
}