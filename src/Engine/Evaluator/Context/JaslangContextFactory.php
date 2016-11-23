<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

use Ehimen\Jaslang\Engine\Evaluator\OutputBuffer;
use Ehimen\Jaslang\Engine\Evaluator\SymbolTable\SymbolTable;
use Ehimen\Jaslang\Engine\Type\TypeRepository;

class JaslangContextFactory implements ContextFactory
{
    /**
     * @var TypeRepository
     */
    private $typeRepository;
    
    public function __construct(TypeRepository $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }
    
    public function createContext()
    {
        return new JaslangContext(new SymbolTable(), $this->typeRepository, new OutputBuffer());
    }
}
