<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

use Ehimen\Jaslang\Engine\Evaluator\OutputBuffer;
use Ehimen\Jaslang\Engine\Evaluator\SymbolTable\SymbolTable;
use Ehimen\Jaslang\Engine\Exception\LogicException;
use Ehimen\Jaslang\Engine\Type\TypeRepository;

class JaslangContextStack implements ContextStack
{
    /**
     * @var EvaluationContext[]
     */
    private $contexts = [];

    /**
     * @var OutputBuffer
     */
    private $outputBuffer;
    
    public function __construct(SymbolTable $base, OutputBuffer $outputBuffer)
    {
        $this->outputBuffer = $outputBuffer;
        $this->contexts = [new JaslangContext($base, $outputBuffer)];
    }
    
    public function createContext()
    {
        return $this->contexts[] = new JaslangContext(
            new SymbolTable($this->getContext()->getSymbolTable()),
            $this->outputBuffer
        );
    }

    public function popContext()
    {
        if (count($this->contexts) <= 1) {
            throw new LogicException('No contexts to pop');
        }
        
        array_pop($this->contexts);
    }

    public function getContext()
    {
        return end($this->contexts);
    }


    public function reset()
    {
        $this->contexts = array_slice($this->contexts, 0, 1);
    }
}
