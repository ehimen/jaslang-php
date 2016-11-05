<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Trace;

class EvaluationTrace
{
    /**
     * @var TraceEntry[]
     */
    private $trace = [];

    public function __construct(array $trace = [])
    {
        $this->trace = $trace;
    }

    public function getAsString()
    {
        return implode(
            PHP_EOL,
            array_map(
                function (TraceEntry $entry, $index) {
                    return '#' . $index . ' > ' . $entry->toString();
                },
                $this->trace,
                array_keys($this->trace)
            )
        );
    }
    
    public function push(TraceEntry $entry)
    {
        array_push($this->trace, $entry);
    }

    public function pop()
    {
        array_pop($this->trace);
    }
}
