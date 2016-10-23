<?php

namespace Ehimen\Jaslang\Parser\Exception;

use Ehimen\Jaslang\Exception\EvaluationException;

class SyntaxErrorException extends EvaluationException 
{
    public function __construct($input)
    {
        parent::__construct($input);
    }
}