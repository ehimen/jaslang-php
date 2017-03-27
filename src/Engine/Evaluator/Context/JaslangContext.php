<?php

namespace Ehimen\Jaslang\Engine\Evaluator\Context;

use Ehimen\Jaslang\Engine\Ast\Node\Node;
use Ehimen\Jaslang\Engine\Evaluator\Exception\TypeErrorException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedSymbolException;
use Ehimen\Jaslang\Engine\Evaluator\OutputBuffer;
use Ehimen\Jaslang\Engine\Evaluator\SymbolTable\SymbolTable;
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
     * @var OutputBuffer
     */
    private $outputBuffer;
    
    public function __construct(SymbolTable $symbolTable, OutputBuffer $outputBuffer)
    {
        $this->symbolTable    = $symbolTable;
        $this->outputBuffer   = $outputBuffer;
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
    public function getVariableOfTypeOrThrow($name, Type $type)
    {
        try {
            $value = $this->symbolTable->get($name);
        } catch (OutOfBoundsException $e) {
            throw new UndefinedSymbolException($name);
        }
        
        $valueType = $this->symbolTable->getTypeByValue($value);
        
        if (!$valueType->isA($type)) {
            throw TypeErrorException::valueTypeMismatch(
                $this->getSymbolTable()->getTypeName($type),
                $this->getSymbolTable()->getTypeName($valueType),
                $value
            );
        }
        
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getOutputBuffer()
    {
        return $this->outputBuffer;
    }
}
