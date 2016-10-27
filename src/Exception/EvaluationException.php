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
    protected $input;
    
    public function __construct($input, $message = '')
    {
        $this->message = $message;
        $this->input   = $input;
    }

    /**
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }
}