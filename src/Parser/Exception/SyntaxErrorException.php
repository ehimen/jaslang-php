<?php

namespace Ehimen\Jaslang\Parser\Exception;

use Ehimen\Jaslang\Exception\EvaluationException;

class SyntaxErrorException extends EvaluationException 
{
    public function __construct($input, $message = '')
    {
        parent::__construct($input, 'Jaslang syntax error! ' . $message);
    }
}