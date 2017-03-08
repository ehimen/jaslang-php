<?php

namespace Ehimen\Jaslang\Engine\Evaluator\InputSteam;

/**
 * Reads input from stdin.
 */
class InputStreamStdIn implements InputStream
{
    /** @var resource */
    private $handler;

    /**
     * @inheritdoc
     */
    public function consume()
    {
        return fread($this->getResource(), 1024);
    }

    /**
     * Get the stdin resource.
     *
     * @return resource
     */
    private function getResource()
    {
        if (!is_resource($this->handler)) {
            $this->handler = fopen('php://stdin', 'r');
        }

        return $this->handler;
    }
}