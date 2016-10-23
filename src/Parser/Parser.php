<?php

namespace Ehimen\Jaslang\Parser;

use Ehimen\Jaslang\Ast\Node;

interface Parser
{
    /**
     * @param string
     * 
     * @return Node
     */
    public function parse($input);
}