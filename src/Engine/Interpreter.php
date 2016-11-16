<?php

namespace Ehimen\Jaslang\Engine;

use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\Parser\Parser;

class Interpreter
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Evaluator
     */
    private $evaluator;

    public function __construct(Parser $parser, Evaluator $evaluator)
    {
        $this->parser    = $parser;
        $this->evaluator = $evaluator;
    }

    /**
     * @param $input
     *
     * @return string
     * @throws RuntimeException
     */
    public function run($input)
    {
        $ast = $this->parser->parse($input);
        
        $this->evaluator->reset();
        
        try {
            $ast->accept($this->evaluator);
        } catch (RuntimeException $e) {
            $e->setEvaluationTrace($this->evaluator->getTrace());
            $e->setInput($input);
            throw $e;
        }

        return $this->evaluator->getResult()->toString();
    }
}
