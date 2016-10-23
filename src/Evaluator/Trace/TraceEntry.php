<?php

namespace Ehimen\Jaslang\Evaluator\Trace;

use Ehimen\Jaslang\Ast\ParentNode;

class TraceEntry
{
    private $details;
    
    public function __construct($details)
    {
        $this->details = $details;
    }
}