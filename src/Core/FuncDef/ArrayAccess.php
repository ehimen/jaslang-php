<?php

namespace Ehimen\Jaslang\Core\FuncDef;

use Ehimen\Jaslang\Engine\Ast\Node\Identifier;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\Exception\OutOfBoundsException;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Any;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expression;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeIdentifier;
use Ehimen\Jaslang\Engine\FuncDef\VariableArgFuncDef;
use Ehimen\Jaslang\Core\Value;

class ArrayAccess implements VariableArgFuncDef
{
    public function getParameters()
    {
        return [
            Parameter::any(),
        ];
    }

    public function invoke(ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        /** @var Any $firstArg */
        $firstArg = $args->get(0);
        
        $node = $firstArg->getNode();
        
        if (!($node instanceof Identifier)) {
            // TODO: test this!
            throw new InvalidArgumentException('Array access expects identifier');
        }
        
        if ($context->getTypeRepository()->hasTypeByName($node->getName())) {
            // Handle array initialisation, i.e.: type[size].
            $second = $args->get(1);
            $size = ($second instanceof Value\Num) ? $second->getValue() : 0;
            $type = $context->getTypeRepository()->getTypeByName($node->getName());

            return new Value\ArrayInitialisation($type, $size);
        }
        
        if ($context->getSymbolTable()->has($node->getName())) {
            // Handle array access.
            $second = $args->get(1);
            
            if (!($second instanceof Value\Num)) {
                $this->illegalArrayAccess();
            }
            
            /** @var Value\Arr $array */
            $array = $context->getSymbolTable()->get($node->getName());
            
            return $array->get($second->getValue());
        }
        
        $this->illegalArrayAccess();
    }

    private function illegalArrayAccess()
    {
        throw new InvalidArgumentException('Illegal array access');
    }
}
