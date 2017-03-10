<?php

namespace Ehimen\Jaslang\Engine\Evaluator\InputSteam;

/**
 * Streams content from an input.
 */
interface InputStream
{
    /**
     * Consume content from the stream.
     *
     * @return string
     */
    public function consume();
}