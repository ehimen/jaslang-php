<?php

namespace Ehimen\Jaslang\Core\Value;

use Ehimen\Jaslang\Engine\Ast\Node\Block;
use Ehimen\Jaslang\Engine\Ast\Node\Expression;
use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\TypedParameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Routine;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypedVariable;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Void;
use Ehimen\Jaslang\Engine\Value\CallableValue;

class LambdaExpression implements CallableValue
{
    /**
     * @var TypedVariable[]
     */
    private $parameters;

    /**
     * @var Routine
     */
    private $body;
    
    public function __construct(array $parameters, Routine $body)
    {
        $this->parameters = $parameters;
        $this->body = $body;
    }


    public static function voidExpr()
    {
        return new static([], new Routine(new Block()));
    }

    public function getExpectedParameters()
    {
        return $this->parameters;
    }

    public function invoke(ArgList $args, Evaluator $evaluator)
    {
        $evaluator->pushContext();
        
        foreach ($this->parameters as $i => $parameter) {
            $value = $args->get($i);
            $evaluator->getContext()->getSymbolTable()->set($parameter->getIdentifier(), $value);
        }
        
        $result = $evaluator->evaluateInIsolation($this->body->getRoutine());
        $evaluator->popContext();
        
        // If we explicitly use the "return" operator, return it.
        if ($result instanceof ExplicitReturn) {
            return $result->getWrapped();
        }
        
        // If we have an expression as our body (i.e. not a block), return it.
        if ($this->body->getRoutine() instanceof Expression) {
            return $result;
        }
        
        // TODO: lambda.jslt#one-line-returning-lambda
        // TODO: some AST generation issues with lambda and one-liner arithmetic.
        // TODO: e.g. let fn : lambda = (num : number) => num * 2
        
        return new Void();
    }


    public function toString()
    {
        return '[lambda]';
    }
}
