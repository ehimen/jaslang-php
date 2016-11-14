<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

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
    
    public function createContext(\Closure $evaluationFn)
    {
        return new JaslangContext(new SymbolTable(), $this->typeRepository, $evaluationFn);
    }
}
