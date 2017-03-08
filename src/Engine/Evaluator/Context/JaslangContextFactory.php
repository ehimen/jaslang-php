<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

use Ehimen\Jaslang\Engine\Evaluator\InputSteam\InputSteamFactory;
use Ehimen\Jaslang\Engine\Evaluator\InputSteam\InputStream;
use Ehimen\Jaslang\Engine\Evaluator\OutputBuffer;
use Ehimen\Jaslang\Engine\Evaluator\SymbolTable\SymbolTable;
use Ehimen\Jaslang\Engine\Type\TypeRepository;

class JaslangContextFactory implements ContextFactory
{
    /**
     * @var TypeRepository
     */
    private $typeRepository;

    /**
     * @var InputStreamFactory
     */
    private $inputStreamFactory;

    private $inputSteam;

    public function __construct(TypeRepository $typeRepository, InputSteamFactory $inputStreamFactory)
    {
        $this->typeRepository     = $typeRepository;
        $this->inputStreamFactory = $inputStreamFactory;
    }
    
    public function createContext()
    {
        return new JaslangContext(new SymbolTable(), $this->typeRepository, new OutputBuffer(), $this->getInputSteam());
    }

    /**
     * When we extend a context, we create a new symbol table, giving
     * variable scope isolation.
     */
    public function extendContext(EvaluationContext $base)
    {
        return new JaslangContext(new SymbolTable(), $this->typeRepository, $base->getOutputBuffer(), $this->getInputSteam());
    }

    private function getInputSteam()
    {
        if (!($this->inputSteam instanceof InputStream)) {
            $this->inputSteam = $this->inputStreamFactory->create();
        }

        return $this->inputSteam;
    }
}
