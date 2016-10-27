<?php

namespace Ehimen\Jaslang\Evaluator;

use Ehimen\Jaslang\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Exception\OutOfBoundsException;
use Ehimen\Jaslang\FuncDef\BinaryFunction;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Operator\Operator;

/**
 * TODO: Type-hinting here on BinaryFunction will be limiting. Need more general OperatorFunction?
 */
class FunctionRepository
{
    const OPERATOR_PRECEDENCE_DEFAULT = 0;

    /**
     * @var FuncDef[]
     */
    private $functions = [];

    /**
     * @var BinaryFunction[]
     */
    private $operators = [];

    /**
     * @var int
     */
    private $operatorPrecedence = [];

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

    public function registerOperator($identifier, BinaryFunction $operator, $precedence = self::OPERATOR_PRECEDENCE_DEFAULT)
    {
        if (isset($this->operators[$identifier])) {
            throw new InvalidArgumentException(
                'Operator with identifier "%s" is already registered',
                $identifier
            );
        }

        $this->operatorPrecedence[$identifier] = $precedence;
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
     * @return int
     */
    public function getOperatorPrecedence($identifier)
    {
        if (isset($this->operatorPrecedence[$identifier])) {
            return $this->operatorPrecedence[$identifier];
        }

        return static::OPERATOR_PRECEDENCE_DEFAULT;
    }
}
