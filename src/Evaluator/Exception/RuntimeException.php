<?php

namespace Ehimen\Jaslang\Evaluator\Exception;

use Ehimen\Jaslang\Evaluator\Trace\EvaluationTrace;
use Ehimen\Jaslang\Exception\EvaluationException;

/**
 * An exception encountered whilst evaluating Jaslang input.
 */
class RuntimeException extends EvaluationException 
{
    private $trace;

    public function __construct($message)
    {
        // Will set input later.
        parent::__construct(null, 'Jaslang runtime exception! ' . $message);
    }
    
    public function setStackTrace(EvaluationTrace $trace)
    {
        $this->trace = $trace;
    }

    public function setInput($input)
    {
        $this->input = $input;
    }
}