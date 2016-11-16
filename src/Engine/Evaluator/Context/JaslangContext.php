<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

use Ehimen\Jaslang\Engine\Ast\Node\Node;
use Ehimen\Jaslang\Engine\Evaluator\Exception\TypeErrorException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedSymbolException;
use Ehimen\Jaslang\Engine\Exception\OutOfBoundsException;
use Ehimen\Jaslang\Engine\Type\Type;
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

    /**
     * @var \Closure
     */
    private $evaluationFn;
    
    public function __construct(SymbolTable $symbolTable, TypeRepository $typeRepository)
    {
        $this->symbolTable    = $symbolTable;
        $this->typeRepository = $typeRepository;
    }

    /**
     * @inheritdoc
     */
    public function getSymbolTable()
    {
        return $this->symbolTable;
    }

    /**
     * @inheritdoc
     */
    public function getTypeRepository()
    {
        return $this->typeRepository;
    }

    /**
     * @inheritdoc
     */
    public function getVariableOfTypeOrThrow($name, Type $type)
    {
        try {
            $value = $this->symbolTable->get($name);
        } catch (OutOfBoundsException $e) {
            throw new UndefinedSymbolException($name);
        }
        
        $valueType = $this->typeRepository->getTypeByValue($value);
        
        if (!$valueType->isA($type)) {
            throw TypeErrorException::valueTypeMismatch(
                $this->typeRepository->getTypeName($type),
                $this->typeRepository->getTypeName($valueType),
                $value
            );
        }
        
        return $value;
    }
}
