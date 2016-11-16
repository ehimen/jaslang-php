<?php

namespace Ehimen\Jaslang\Engine\Parser;

use Ehimen\Jaslang\Engine\Ast\Node\Node;
use Ehimen\Jaslang\Engine\Lexer\Token;

interface NodeCreationObserver
{
    public function onNodeCreated(Node $node, Token $currentToken);
}
