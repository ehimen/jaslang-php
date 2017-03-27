<?php

namespace Ehimen\Jaslang\Engine\FuncDef;

use Ehimen\Jaslang\Engine\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\Exception\OutOfBoundsException;

/**
 * Repository for all native constructs provided by a language.
 * 
 * This includes functions, operators and list operators, which all
 * FuncDefs, just take different forms.
 * 
 * A function is an identifier, invoked when followed by an arg list, e.g. foo(arg1, arg2).
 * An operator is a token or keyword, which take zero or mode operands on either side, e.g. operand1 + operand2.
 * List operations are enclosing structures around a variable-length of element, e.g. [element1, element2].
 */
class FunctionRepository
{
    /**
     * @var FuncDef[]
     */
    private $functions = [];

    /**
     * @var FuncDef[]
     */
    private $operators = [];

    /**
     * @var FuncDef[]
     */
    private $listOperators = [];

    /**
     * @var OperatorSignature[]
     */
    private $operatorSignatures = [];

    /**
     * @var ListOperatorSignature[]
     */
    private $listOperatorSignatures = [];

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
        FuncDef $operator,
        OperatorSignature $signature
    ) {
        if (isset($this->operators[$identifier])) {
            throw new InvalidArgumentException(sprintf(
                'Operator with identifier "%s" is already registered',
                $identifier
            ));
        }

        $this->operatorSignatures[$identifier] = $signature;
        $this->operators[$identifier]          = $operator;
    }

    public function registerListOperation(FuncDef $operation, ListOperatorSignature $signature)
    {
        $open = $signature->getEnclosureStart();

        $this->listOperators[$open]          = $operation;
        $this->listOperatorSignatures[$open] = $signature;
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
     * @return FuncDef
     */
    public function getOperator($identifier)
    {
        if (!isset($this->operators[$identifier])) {
            throw new OutOfBoundsException(sprintf('Operator with identifier "%s" not found', $identifier));
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
            throw new OutOfBoundsException(sprintf('Operator signature with identifier "%s" not found', $identifier));
        }

        return $this->operatorSignatures[$identifier];
    }

    public function getListOperation($open)
    {
        if (!isset($this->listOperators[$open])) {
            throw new OutOfBoundsException(sprintf('List operation opening with "%s" not found', $open));
        }
        
        return $this->listOperators[$open];
    }

    public function getListOperatorSignature($open)
    {
        if (!isset($this->listOperatorSignatures[$open])) {
            throw new OutOfBoundsException(sprintf('List operation opening with %s not found', $open));
        }

        return $this->listOperatorSignatures[$open];
    }
}
