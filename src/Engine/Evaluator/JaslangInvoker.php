<?php

namespace Ehimen\Jaslang\Engine\Evaluator;

use Ehimen\Jaslang\Core\Type\Any;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\Evaluator\SymbolTable\SymbolTable;
use Ehimen\Jaslang\Engine\Exception\LogicException;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Collection;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\TypedParameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Routine;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expression;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypedVariable;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeResolvingArg;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Variable;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\Value\CallableValue;
use Ehimen\Jaslang\Engine\Value\Value;

/**
 * Dispatches function invocations, validating arguments along the way.
 */
class JaslangInvoker implements Invoker
{

    public function invokeFunction(FuncDef $function, ArgList $args, Evaluator $evaluator)
    {
        $signature = new \ReflectionClass($function);
        $methods = $signature->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        foreach ($methods as $method) {
            $parameters = $method->getParameters();
            
            if (empty($parameters)) {
                // TODO: Could actually make the requirement for evaluator to be optional.
                // Would make it less explicit, but cleaner FuncDef implementations.
                continue;
            }
            
            $first = current($parameters);
            
            if (!$first->getClass() || !is_a($first->getClass()->getName(), Evaluator::class, true)) {
                continue;
            }
            
            $toApply = [];
            
            foreach (array_slice($parameters, 1) as $i => $parameter) {
                /** @var \ReflectionParameter $parameter */
                $arg = $args->get($i);
                
                if ($parameter->allowsNull() && ($arg === null)) {
                    // TODO: what about default values etc?
                    $toApply[] = null;
                    continue;
                }
                
                if (is_a($arg, $parameter->getClass()->getName(), true)) {
                    $toApply[] = $arg;
                    continue;
                }
                
                // This method is not compatible with the provided arguments;
                // move on to the next.
                continue 2;
            }
            
            return $method->invoke($function, $evaluator, ...$args->all());
        }
        
        // TODO: defer to funcdef to print something more useful?
        throw new InvalidArgumentException('Could not resolve arguments to an operation');
        
        // TODO: return type. Really need to validate this. Keep not returning wrapped values!
        // TODO: Move to PHP7, then interrogate the return type to make sure that it is an Argument
        // TODO: If not, skip it in the reflection checks.
    }

    public function invokeCallable(
        CallableValue $value,
        ArgList $args,
        Evaluator $evaluator
    ) {
        $parameters = array_map(
            function (TypedVariable $variable) use ($evaluator) {
                $type = $evaluator->getContext()->getSymbolTable()->getType($variable->getTypeIdentifier());
                
                return TypedParameter::value($type);
            },
            $value->getExpectedParameters()
        );
        
        $this->validateArgs($parameters, $args, $evaluator->getContext()->getSymbolTable());
        
        return $value->invoke($args, $evaluator);
    }


    /**
     * @param Parameter[] $argDefs
     * @param ArgList     $args
     *
     * @throws InvalidArgumentException
     */
    private function validateArgs(array $argDefs, ArgList $args, SymbolTable $symbolTable)
    {
        if ($args->count() > count($argDefs)) {
            throw InvalidArgumentException::unexpectedArgument(count($argDefs));
        }

        foreach ($argDefs as $i => $def) {
            $arg = $args->get($i);

            if ($def->isValue()) {
                $type = $symbolTable->getTypeName($def->getExpectedType());
            } elseif ($def->isType()) {
                $type = 'type-identifier';
            } elseif ($def->isVariable()) {
                $type = 'variable';
            } elseif ($def->isRoutine()) {
                $type = 'routine';
            } elseif ($def->isExpression()) {
                $type = 'expression';
            } elseif ($def->isCollection()) {
                $type = 'collection';
            } elseif ($def->isAny()) {
                $type = 'any';
            } else {
                throw new LogicException('Cannot handle definition as is not one of variable, type, value or block');
            }

            if (null === $arg) {
                throw InvalidArgumentException::invalidArgument($i, $type, $arg);
            }

            if ($def instanceof TypedParameter) {
                if (!($arg instanceof Value)) {
                    throw InvalidArgumentException::invalidArgument($i, $type, $arg);
                }

                $argType = $symbolTable->getTypeByValue($arg);

                if ($argType && !$argType->isA($def->getExpectedType())) {
                    throw InvalidArgumentException::invalidArgument($i, $type, $arg);
                }
            } elseif ($def->isType() && !($arg instanceof TypeResolvingArg)) {
                throw InvalidArgumentException::invalidArgument($i, $type, $arg);
            } elseif ($def->isVariable() && !($arg instanceof Variable)) {
                throw InvalidArgumentException::invalidArgument($i, $type, $arg);
            } elseif ($def->isRoutine() && !($arg instanceof Routine)) {
                throw InvalidArgumentException::invalidArgument($i, $type, $arg);
            } elseif ($def->isExpression() && !($arg instanceof Expression)) {
                throw InvalidArgumentException::invalidArgument($i, $type, $arg);
            } elseif ($def->isCollection() && !($arg instanceof Collection)) {
                throw InvalidArgumentException::invalidArgument($i, $type, $arg);
                // TODO: validate what is in collection!?
            }
        }
    }
}
