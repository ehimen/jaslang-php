<?php

namespace Ehimen\Jaslang\Engine\FuncDef;

use Ehimen\Jaslang\Engine\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\Exception\OutOfBoundsException;

/**
 * TODO: Type-hinting here on BinaryFunction will be limiting. Need more general OperatorFunction?
 */
class FunctionRepository
{
    /**
     * @var FuncDef[]
     */
    private $functions = [];

    /**
     * @var BinaryFunction[]
     */
    private $operators = [];

    /**
     * @var OperatorSignature[]
     */
    private $operatorSignatures = [];

    public function registerFunction($identifier, FuncDef $func)
    {
        if (isset($this->functions[$identifier])) {
            throw new InvalidArgumentException(
                'Function with identifier "%s" is already registered',
                $identifier
            );
        }
        
        $this->functions[$identifier] = $func;
    }

    public function getFunction($identifier)
    {
        if (!isset($this->functions[$identifier])) {
            throw new OutOfBoundsException('Function with identifier "%s" not found');
        }
        
        return $this->functions[$identifier];
    }

    public function registerOperator(
        $identifier,
        BinaryFunction $operator,
        OperatorSignature $signature
    ) {
        if (isset($this->operators[$identifier])) {
            throw new InvalidArgumentException(
                'Operator with identifier "%s" is already registered',
                $identifier
            );
        }

        $this->operatorSignatures[$identifier] = $signature;
        $this->operators[$identifier]          = $operator;
    }

    /**
     * @return string[]
     */
    public function getRegisteredOperatorIdentifiers()
    {
        return array_keys($this->operators);
    }

    /**
     * @param $identifier
     *
     * @return BinaryFunction
     */
    public function getOperator($identifier)
    {
        if (!isset($this->operators[$identifier])) {
            throw new OutOfBoundsException('Operator with identifier "%s" not found');
        }

        return $this->operators[$identifier];
    }

    /**
     * @param $identifier
     *
     * @return OperatorSignature
     */
    public function getOperatorSignature($identifier)
    {
        if (!isset($this->operatorSignatures[$identifier])) {
            throw new OutOfBoundsException('Operator signature with identifier "%s" not found');
        }

        return $this->operatorSignatures[$identifier];
    }
}
