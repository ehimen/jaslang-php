<?php

namespace Ehimen\Jaslang\Evaluator;

use Ehimen\Jaslang\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\FuncDef\ArgDef;
use Ehimen\Jaslang\FuncDef\ArgList;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Type\Type;

/**
 * Dispatches function invocations, validating arguments along the way.
 */
class JaslangInvoker implements Invoker
{
    /**
     * @var TypeRepository
     */
    private $repository;
    
    public function __construct(TypeRepository $repository)
    {
        $this->repository = $repository;
    }
    
    public function invokeFunction(FuncDef $function, ArgList $args, EvaluationContext $context)
    {
        $this->validateArgs($function->getArgDefs(), $args);
        
        return $function->invoke($args, $context);
        
        // TODO: return type. Really need to validate this. Keep not returning wrapped values!
    }

    /**
     * @param ArgDef[] $argDefs
     * @param ArgList $args
     */
    private function validateArgs(array $argDefs, ArgList $args)
    {
        // TODO: validate not too many!
        foreach ($argDefs as $i => $def) {
            $arg  = $args->get($i);
            $type = $this->repository->getTypeName($def->getType());
            
            if (null === $arg) {
                if ($def->isOptional()) {
                    continue;
                }

                throw new InvalidArgumentException($i, $type, $arg);
            }
            
            $argType = $this->repository->getTypeByValue($arg);
            
            if (!$this->typesMatch($def->getType(), $argType)) {
                throw new InvalidArgumentException($i, $type, $arg);
            }
        }
    }

    /**
     * TODO: extract to dedicated class.
     * 
     * @param $expected
     * @param $actual
     * 
     * @return bool
     */
    private function typesMatch(Type $expected, Type $actual)
    {
        do {
            if (get_class($expected) === get_class($actual)) {
                return true;
            }
            
            $actual = $actual->getParent();
        } while ($actual instanceof Type);
        
        return false;
    }
}
