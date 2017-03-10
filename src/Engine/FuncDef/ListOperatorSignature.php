<?php

namespace Ehimen\Jaslang\Engine\FuncDef;

/**
 * Special type of operator which enclose arguments within, as well as having arguments either side.
 */
class ListOperatorSignature extends OperatorSignature
{
    /**
     * @var string
     *
     * The character(s) that open the list.
     */
    private $enclosureStart;

    /**
     * @var string
     *
     * The character(s) that close the list.
     */
    private $enclosureEnd;

    public static function create($enclosureStart, $enclosureEnd, $leftArgs, $rightArgs, $precedence = self::OPERATOR_PRECEDENCE_DEFAULT)
    {
        $operation = static::arbitrary($leftArgs, $rightArgs, $precedence);

        $operation->enclosureStart = $enclosureStart;
        $operation->enclosureEnd   = $enclosureEnd;

        return $operation;
    }

    /**
     * @return string
     */
    public function getEnclosureStart()
    {
        return $this->enclosureStart;
    }

    /**
     * @return string
     */
    public function getEnclosureEnd()
    {
        return $this->enclosureEnd;
    }
}
