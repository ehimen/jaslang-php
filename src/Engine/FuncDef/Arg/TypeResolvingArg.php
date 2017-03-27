<?php

namespace Ehimen\Jaslang\Engine\FuncDef\Arg;

use Ehimen\Jaslang\Engine\Type\TypeRepository;

/**
 * An argument that can be resolved to a type.
 */
interface TypeResolvingArg extends Argument
{
    /**
     * @return \Ehimen\Jaslang\Engine\Type\Type
     */
    public function resolve(TypeRepository $typeRepository);
}
