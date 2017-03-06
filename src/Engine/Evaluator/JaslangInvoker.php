<?php

namespace Ehimen\Jaslang\Engine\Evaluator;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\Exception\LogicException;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Routine;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expression;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypedVariable;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeIdentifier;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Variable;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
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

    public function invokeFunction(FuncDef $function, ArgList $args, EvaluationContext $context, Evaluator $evaluator)
    {
        $this->validateArgs($function->getParameters(), $args);

        return $function->invoke($args, $context, $evaluator);

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
        if ($args->count() > count($argDefs)) {
            throw InvalidArgumentException::unexpectedArgument(count($argDefs));
        }

        foreach ($argDefs as $i => $def) {
            $arg = $args->get($i);

            if ($def->isValue()) {
                $type = $this->repository->getTypeName($def->getExpectedType());
            } elseif ($def->isType()) {
                $type = 'type-identifier';
            } elseif ($def->isVariable()) {
                $type = 'variable';
            } elseif ($def->isRoutine()) {
                $type = 'routine';
            } elseif ($def->isExpression()) {
                $type = 'expression';
            } elseif ($def->isTypedVariable()) {
                $type = 'typed-variable';
            } else {
                throw new LogicException('Cannot handle definition as is not one of variable, type, value or block');
            }

            if (null === $arg) {
                if ($def->isOptional()) {
                    continue;
                }

                throw InvalidArgumentException::invalidArgument($i, $type, $arg);
            }

            if ($def->isValue()) {
                if (!($arg instanceof Value)) {
                    throw InvalidArgumentException::invalidArgument($i, $type, $arg);
                }

                $argType = ($arg instanceof Value)
                    ? $this->repository->getTypeByValue($arg)
                    : $arg->getType();

                if ($argType && !$argType->isA($def->getExpectedType())) {
                    throw InvalidArgumentException::invalidArgument($i, $type, $arg);
                }
            } elseif ($def->isType() && !($arg instanceof TypeIdentifier)) {
                throw InvalidArgumentException::invalidArgument($i, $type, $arg);
            } elseif ($def->isVariable() && !($arg instanceof Variable)) {
                throw InvalidArgumentException::invalidArgument($i, $type, $arg);
            } elseif ($def->isRoutine() && !($arg instanceof Routine)) {
                throw InvalidArgumentException::invalidArgument($i, $type, $arg);
            } elseif ($def->isExpression() && !($arg instanceof Expression)) {
                throw InvalidArgumentException::invalidArgument($i, $type, $arg);
            } elseif ($def->isTypedVariable() && !($arg instanceof TypedVariable)) {
                throw InvalidArgumentException::invalidArgument($i, $type, $arg);
            }
        }
    }
}
