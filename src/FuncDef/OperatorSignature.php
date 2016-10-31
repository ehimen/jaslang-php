<?php

namespace Ehimen\Jaslang\FuncDef;

class OperatorSignature
{
    const OPERATOR_PRECEDENCE_DEFAULT = 0;

    /**
     * @var int
     */
    private $leftArgs;

    /**
     * @var int
     */
    private $rightArgs;

    /**
     * @var int
     */
    private $precedence;

    public function __construct($leftArgs, $rightArgs, $precedence = self::OPERATOR_PRECEDENCE_DEFAULT)
    {
        $this->leftArgs   = $leftArgs;
        $this->rightArgs  = $rightArgs;
        $this->precedence = $precedence;
    }

    public static function binary($precedence = self::OPERATOR_PRECEDENCE_DEFAULT)
    {
        return new static(1, 1, $precedence);
    }

    public static function postfixUnary($precedence = self::OPERATOR_PRECEDENCE_DEFAULT)
    {
        return new static(0, 1, $precedence);
    }

    public static function prefixUnary($precedence = self::OPERATOR_PRECEDENCE_DEFAULT)
    {
        return new static(1, 0, $precedence);
    }

    /**
     * @return int
     */
    public function getLeftArgs()
    {
        return $this->leftArgs;
    }

    /**
     * @return int
     */
    public function getRightArgs()
    {
        return $this->rightArgs;
    }

    /**
     * @return int
     */
    public function getPrecedence()
    {
        return $this->precedence;
    }
}