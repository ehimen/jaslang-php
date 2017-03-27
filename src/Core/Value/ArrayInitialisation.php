<?php

namespace Ehimen\Jaslang\Core\Value;

use Ehimen\Jaslang\Core\Type\Arr;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeResolvingArg;
use Ehimen\Jaslang\Engine\Type\ConcreteType;
use Ehimen\Jaslang\Engine\Type\Type;
use Ehimen\Jaslang\Engine\Type\TypeRepository;

/**
 * Value to indicate array initialisation from ArrayAccess funcdef.
 * 
 * This represents the modification of a type to its array counterpart.
 */
class ArrayInitialisation implements TypeResolvingArg
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var int
     */
    private $size;

    public function __construct(ConcreteType $type, $size)
    {
        $this->type = $type;
        $this->size = $size;
    }

    public function getAsType()
    {
        return new Arr($this->type, $this->size);
    }

    public function getElementType()
    {
        return $this->type;
    }

    public function toString()
    {
        // TODO: need to print type & size?
        return sprintf('array-init');
    }

    public function resolve(TypeRepository $typeRepository)
    {
        return $this->getAsType();
    }
}
