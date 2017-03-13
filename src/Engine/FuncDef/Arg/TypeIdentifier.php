<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Type\TypeRepository;

class TypeIdentifier implements TypeResolvingArg
{
    /**
     * @var string
     */
    private $identifier;

    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    public function toString()
    {
        return '[type] ' . $this->identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function resolve(TypeRepository $typeRepository)
    {
        return $typeRepository->getTypeByName($this->identifier);
    }
}
