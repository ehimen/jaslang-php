<?php

namespace Ehimen\Jaslang\Engine\FuncDef\InvocationResolver;

use Ehimen\Jaslang\Engine\Ast\Node\Node;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\SymbolTable\SymbolTable;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Argument;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\FuncDef\InvocationResolver\Exception\InvocationImpossibleException;

/**
 * Given a FuncDef and a set of nodes, we determine whether or not 
 * the two are compatible.
 */
class InvocationResolver
{
    public function resolveInvocation(FuncDef $funcDef, ArgList $args)
    {
        $types = array_map(
            function (Argument $arg = null) {
                if ($arg === null) {
                    return null;
                }
                
                return get_class($arg);
            },
            $args->all()
        );
        
        return $this->resolve($funcDef, $types);
    }

    public function resolveArguments(FuncDef $funcDef, array $nodes, SymbolTable $symbolTable)
    {
        $arguments = $types = [];
        
        foreach  ($nodes as $node) {
            /** @var Node $node */
            $type = $this->getArgForNode($node);
            $arguments[] = 
        }
        
        $types = array_map(
            function (Node $node) use {
                return $this->getArgTypeForNode($node);
            },
            $nodes
        );
        
        try {
            $this->resolveInvocation($funcDef, $types);
        } catch (InvocationImpossibleException $e) {
            return false;
        }
        
        return true;
    }

    private function getArgForNode(Node $node, SymbolTable $symbolTable)
    {
        
    }

    private function resolve(FuncDef $funcDef, array $argTypes)
    {
        foreach ($this->getMethods($funcDef) as $method) {
            // Iterate over parameters, ignoring the first as its the evaluator.
            foreach (array_slice($method->getParameters(), 1) as $i => $parameter) {
                /** @var \ReflectionParameter $parameter */
                if ($parameter->allowsNull() && !isset($argTypes[$i])) {
                    // An optional parameter, allow if it the arg is null or not defined.
                    continue;
                }
                
                $arg = $argTypes[$i];

                if (!is_a($arg, $parameter->getClass()->getName(), true)) {
                    // Incompatible type; move on to the next method.
                    continue 2;
                }
            }

            return $method;
        }

        throw InvocationImpossibleException::fromFuncDef($funcDef);
    }

    private function getMethods(FuncDef $funcDef)
    {
        $signature = new \ReflectionClass($funcDef);
        $methods   = $signature->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        return array_filter(
            $methods,
            function (\ReflectionMethod $method) {
                if (strpos($method->getName(), '__') === 0)  {
                    // Ignore magics.
                    return false;
                }
                
                if ($method->isStatic() || $method->isAbstract()) {
                    // We only want invocable methods.
                    return false;
                }
                
                $parameters = $method->getParameters();

                // It's only an invokable method if its first parameter accepts the
                // evaluator.
                return (count($parameters) >= 1) && is_a($parameters[0]->getType(), Evaluator::class, true);
            }
        );
    }
}
