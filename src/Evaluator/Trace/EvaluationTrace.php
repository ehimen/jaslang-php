<?php

namespace Ehimen\Jaslang\Evaluator\Trace;

class EvaluationTrace
{
    private $trace = [];
    
    public function push(TraceEntry $entry)
    {
        array_push($this->trace, $entry);
    }

    public function pop()
    {
        array_pop($this->trace);
    }
}