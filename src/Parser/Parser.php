<?php

namespace Ehimen\Jaslang\Parser;

use Ehimen\Jaslang\Ast\Root;

interface Parser
{
    /**
     * @param string
     * 
     * @return Root
     */
    public function parse($input);
}
