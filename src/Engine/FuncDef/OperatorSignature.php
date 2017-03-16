<?php

namespace Ehimen\Jaslang\Engine\FuncDef;

class OperatorSignature
{
    const OPERATOR_PRECEDENCE_DEFAULT = 0;

    /**
     * @var bool
     */
    private $leftArg;

    /**
     * @var int
     */
    private $rightArgs;

    /**
     * @var int
     */
    private $precedence;

    private function __construct($leftArg, $rightArgs, $precedence = self::OPERATOR_PRECEDENCE_DEFAULT)
    {
        $this->leftArg    = (bool)$leftArg;
        $this->rightArgs  = $rightArgs;
        $this->precedence = $precedence;
    }

    /**
     * Create a signature for an infix binary operator, with $precedence.
     *
     * Example: addition, 2 + 3
     *
     * @param int $precedence
     *
     * @return static
     */
    public static function binary($precedence = self::OPERATOR_PRECEDENCE_DEFAULT)
    {
        return static::arbitrary(true, 1, $precedence);
    }

    /**
     * Create a signature for an operator with arbitrary left and right args.
     *
     * @param int $leftArg
     * @param int $rightArgs
     * @param int $precedence
     *
     * @return static
     */
    public static function arbitrary($leftArg, $rightArgs, $precedence = self::OPERATOR_PRECEDENCE_DEFAULT)
    {
        return new static($leftArg, $rightArgs, $precedence);
    }

    /**
     * Create a signature for a postfix unary operator, with $precedence.
     *
     * Example: postfix increment, x++
     *
     * @param int $precedence
     *
     * @return static
     */
    public static function postfixUnary($precedence = self::OPERATOR_PRECEDENCE_DEFAULT)
    {
        return new static(true, 0, $precedence);
    }


    /**
     * Create a signature for a prefix unary operator, with $precedence.
     *
     * Example: prefix increment, ++x
     *
     * @param int $precedence
     *
     * @return static
     */
    public static function prefixUnary($precedence = self::OPERATOR_PRECEDENCE_DEFAULT)
    {
        return new static(false, 1, $precedence);
    }

    /**
     * @return int
     */
    public function hasLeftArg()
    {
        return $this->leftArg;
    }

    /**
     * @return int
     */
    public function getRightArgs()
    {
        return $this->rightArgs;
    }

    public function hasRightArgs()
    {
        return ($this->rightArgs > 0);
    }

    /**
     * @return int
     */
    public function getPrecedence()
    {
        return $this->precedence;
    }

    public function takesPrecedenceOver(self $other)
    {
        return ($this->precedence > $other->precedence);
    }
}
