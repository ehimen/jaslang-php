<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

/**
 * An expression node is any that can be evaluated and a result returned.
 * 
 * This is a tag interface to group different types of node.
 * 
 * Any expression can also be considered a routine as it can be evaluated,
 * but its return value ignored.
 */
interface Expression extends Routine
{
    
}
