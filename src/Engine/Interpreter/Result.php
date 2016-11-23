<?php

namespace Ehimen\Jaslang\Engine\Interpreter;

use Ehimen\Jaslang\Engine\Exception\EvaluationException;

class Result
{
    /**
     * @var string
     */
    private $out;

    /**
     * @var EvaluationException|null
     */
    private $error;
    
    public function __construct($out, EvaluationException $error = null)
    {
        $this->out   = $out;
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getOut()
    {
        return $this->out;
    }

    /**
     * @return EvaluationException|null
     */
    public function getError()
    {
        return $this->error;
    }
}
