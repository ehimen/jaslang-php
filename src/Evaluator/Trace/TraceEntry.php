<?php

namespace Ehimen\Jaslang\Evaluator\Trace;

class TraceEntry
{
    private $details;
    
    public function __construct($details)
    {
        $this->details = $details;
    }

    public function toString()
    {
        return $this->details;
    }
}