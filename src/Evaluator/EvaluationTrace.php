<?php

namespace Ehimen\Jaslang\Evaluator;

class EvaluationTrace
{
    private $trace = [];
    
    public function push($entry)
    {
        array_push($this->trace, $entry);
    }

    public function pop()
    {
        array_pop($this->trace);
    }
}