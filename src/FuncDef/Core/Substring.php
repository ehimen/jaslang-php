<?php

namespace Ehimen\Jaslang\FuncDef\Core;

use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Value\Str;

class Substring extends FuncDef
{
    public function getArgDefs()
    {
        return [
            new ArgDef(ArgDef::STRING, false),
            new ArgDef(ArgDef::NUMBER, false),
            new ArgDef(ArgDef::NUMBER, false),
        ];
    }

    public function invoke(ArgList $args, $context = null)
    {
        return new Str(substr(
            $args->getString(0)->getValue(),
            $args->getNumber(1)->getValue(),
            $args->getNumber(2)->getValue()
        ));
    }
}