<?php

namespace Ehimen\Jaslang\Engine\Parser;

use Ehimen\Jaslang\Engine\Ast\Node\Root;

interface Parser
{
    /**
     * @param string
     *
     * @return Root
     */
    public function parse($input);
}
