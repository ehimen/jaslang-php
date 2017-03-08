<?php

namespace Ehimen\Jaslang\Engine\Parser\Validator;

use Ehimen\Jaslang\Engine\Ast\Node\Root;
use Ehimen\Jaslang\Engine\Parser\Exception\SyntaxErrorException;

interface Validator
{
    /**
     * Performs a post-parse validation over $ast.
     * 
     * Provides an opportunity to catch invalid syntax in input
     * that cannot be caught at first due to the need to be
     * relatively permissive to support generic operators/functions. 
     * 
     * @param string $input
     * @param Root $ast
     * 
     * @throws SyntaxErrorException
     */
    public function validate($input, Root $ast);
}
