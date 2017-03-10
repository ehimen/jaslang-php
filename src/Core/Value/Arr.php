<?php

namespace Ehimen\Jaslang\Core\Value;

use Ehimen\Jaslang\Engine\Type\Type;
use Ehimen\Jaslang\Engine\Value\Printable;
use Ehimen\Jaslang\Engine\Value\Value;

class Arr implements Printable
{
    /**
     * @var int
     */
    private $size;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var Value[]
     */
    private $elements = [];
    
    public function __construct($size, Type $type)
    {
        $this->size = $size;
        $this->type = $type;
    }

    public function set($index, $type)
    {
        // TODO: assert in bounds.
        $this->elements[$index] = $type;
    }

    public function toString()
    {
        return sprintf(
            '[%s]',
            implode(
                ', ',
                array_map(
                    function (Value $value) {
                        return $value->toString();
                    },
                    $this->elements
                )
            )
        );
    }

    public function printValue()
    {
        return $this->toString();
    }
}
