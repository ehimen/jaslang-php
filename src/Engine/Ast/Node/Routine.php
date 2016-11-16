<?php

namespace Ehimen\Jaslang\Engine\Ast\Node;

/**
 * A routine is a node that can be executed but does not evaluate to a single value.
 *
 * This is a tag interface to group different types of node.
 * 
 * TODO: Technically everything returns at the moment. Let's fix that.
 */
interface Routine extends Node
{

}
