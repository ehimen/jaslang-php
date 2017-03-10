<?php

namespace Ehimen\Jaslang\Engine\FuncDef;

/**
 * A variant of a func def that indicates that variable args are provided.
 * 
 * Implementing this interface will disable argument validation before a
 * func def is invoked.
 */
interface VariableArgFuncDef extends FuncDef
{

}
