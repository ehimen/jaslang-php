<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

use Ehimen\Jaslang\Engine\Type\TypeRepository;

class JaslangContext implements EvaluationContext 
{
    /**
     * @var SymbolTable
     */
    private $symbolTable;

    /**
     * @var TypeRepository
     */
    private $typeRepository;
    
    public function __construct(SymbolTable $symbolTable, TypeRepository $typeRepository)
    {
        $this->symbolTable    = $symbolTable;
        $this->typeRepository = $typeRepository;
    }

    public function getSymbolTable()
    {
        return $this->symbolTable;
    }

    public function getTypeRepository()
    {
        return $this->typeRepository;
    }
}