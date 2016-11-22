<?php

namespace Ehimen\Jaslang\Engine\Parser\Exception;

use Ehimen\Jaslang\Engine\Exception\EvaluationException;

class SyntaxErrorException extends EvaluationException
{
    public function __construct($input, $message = '')
    {
        parent::__construct($input, 'Jaslang syntax error! Input: ' . $input . PHP_EOL . $message);
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->getMessage();
    }
}
