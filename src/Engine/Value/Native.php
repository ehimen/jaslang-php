<?php

namespace Ehimen\Jaslang\Engine\Value;

/**
 * A value whose type is native to PHP.
 *
 * This is convenience for simple types which wrap a native value.
 */
abstract class Native implements Printable
{
    protected $value;
    
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return (string)$this->value;
    }

    /**
     * @inheritdoc
     */
    public function printValue()
    {
        return $this->toString();
    }
}
