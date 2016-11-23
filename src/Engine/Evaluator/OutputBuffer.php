<?php

namespace Ehimen\Jaslang\Engine\Evaluator;

/**
 * Buffers content for output.
 * 
 * This effectively models stdout.
 */
class OutputBuffer
{
    private $content = '';

    /**
     * Writes content to the buffer.
     * 
     * @param string $content
     */
    public function write($content)
    {
        $this->content .= $content;
    }

    /**
     * @return string
     */
    public function get()
    {
        return $this->content;
    }
}
