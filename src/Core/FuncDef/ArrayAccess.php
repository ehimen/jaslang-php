<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Ast\Node\Identifier;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\Exception\OutOfBoundsException;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expression;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeIdentifier;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Variable;
use Ehimen\Jaslang\Engine\FuncDef\VariableArgFuncDef;
use Ehimen\Jaslang\Core\Value;

class ArrayAccess implements VariableArgFuncDef
{
    public function getParameters()
    {
        return [
            Parameter::variable(),
        ];
    }

    public function initialisation(Evaluator $evaluator, TypeIdentifier $identifier, Value\Num $index = null)
    {
        $index = ($index instanceof Value\Num) ? $index->getValue() : 0;
        $type  = $evaluator->getContext()->getTypeRepository()->getTypeByName($identifier->getIdentifier());
        
        return new Value\ArrayInitialisation($type, $index);
    }

    public function access(Evaluator $evaluator, Variable $variable, Value\Num $index)
    {
        /** @var Value\Arr $array */
        $array = $evaluator->getContext()->getSymbolTable()->get($variable->getIdentifier())->getDatum();

        return $array->get($index->getValue());
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        /** @var Variable $firstArg */
        $firstArg = $args->get(0);

        $identifier = $firstArg->getIdentifier();
        
        $this->illegalArrayAccess();
    }

    private function illegalArrayAccess()
    {
        throw new InvalidArgumentException('Illegal array access');
    }
}
