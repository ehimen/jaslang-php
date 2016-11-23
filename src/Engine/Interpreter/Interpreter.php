<?php

namespace Ehimen\Jaslang\Engine\Interpreter;

use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\Exception\EvaluationException;
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
     * @return Result
     */
    public function run($input)
    {
        $this->evaluator->reset();
        
        $error = null;
        
        try {
            $ast = $this->parser->parse($input);
            $ast->accept($this->evaluator);
        } catch (EvaluationException $e) {
            if ($e instanceof RuntimeException) {
                $e->setEvaluationTrace($this->evaluator->getTrace());
                $e->setInput($input);
            }
            $error = $e;
        }
        
        return new Result(
            $this->evaluator->getContext()->getOutputBuffer()->get(),
            $error
        );
    }
}
