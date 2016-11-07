<?php

namespace Ehimen\Jaslang\Engine\Evaluator;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\Type\Type;
use Ehimen\Jaslang\Engine\Type\TypeRepository;
use Ehimen\Jaslang\Engine\Value\Value;

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
        $this->validateArgs($function->getParameters(), $args);
        
        return $function->invoke($args, $context);
        
        // TODO: return type. Really need to validate this. Keep not returning wrapped values!
    }

    /**
     * @param Parameter[] $argDefs
     * @param ArgList     $args
     *
     * @throws InvalidArgumentException
     */
    private function validateArgs(array $argDefs, ArgList $args)
    {
        // TODO: validate not too many!
        foreach ($argDefs as $i => $def) {
            $arg = $args->get($i);

            if ($def->isValue() || $def->isVariable()) {
                $type = $this->repository->getTypeName($def->getExpectedType());
            } else {
                // Must be expected a type identifier.
                $type = 'type-identifier';
            }

            if (null === $arg) {
                if ($def->isOptional()) {
                    continue;
                }

                throw new InvalidArgumentException($i, $type, $arg);
            }

            if ($def->isValue()) {
                if (!($arg instanceof Value)) {
                    throw new InvalidArgumentException($i, $type, $arg);
                }

                $argType = $this->repository->getTypeByValue($arg);

                if (!$this->typesMatch($def->getExpectedType(), $argType)) {
                    throw new InvalidArgumentException($i, $type, $arg);
                }
            }
        }
    }

    /**
     * TODO: extract to dedicated class.
     *
     * @param Type $expected
     * @param Type|null $actual
     *
     * @return bool
     */
    private function typesMatch(Type $expected, Type $actual = null)
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
