<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Exception;

use Ehimen\Jaslang\Engine\Evaluator\Trace\EvaluationTrace;
use Ehimen\Jaslang\Engine\Exception\EvaluationException;

/**
 * An exception encountered whilst evaluating Jaslang input.
 */
class RuntimeException extends EvaluationException
{
    protected $evaluationTrace;

    public function __construct($message)
    {
        // Will set input later.
        parent::__construct(null, 'Jaslang runtime exception! ' . $message);
    }
    
    public function setEvaluationTrace(EvaluationTrace $trace)
    {
        $this->evaluationTrace = $trace;
    }

    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * @return EvaluationTrace
     */
    public function getEvaluationTrace()
    {
        return $this->evaluationTrace;
    }
    
    public function __toString()
    {
        return sprintf(
            '%s%s%s',
            $this->getMessage(),
            PHP_EOL,
            $this->getEvaluationTrace()->getAsString()
        );
    }
}
