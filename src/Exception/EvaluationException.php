<?php

namespace Ehimen\Jaslang\Exception;

/**
 * Any exception that is a result of processing Jaslang.
 * 
 * This indicates an error with some Jaslang input, not
 * a programming error in the Jaslang library.
 */
class EvaluationException extends \Exception
{
    private $input;
    
    public function __construct($input)
    {
        $this->input = $input;
    }
}