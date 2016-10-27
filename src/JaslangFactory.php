<?php

namespace Ehimen\Jaslang;

use Ehimen\Jaslang\Evaluator\Evaluator;
use Ehimen\Jaslang\Evaluator\JaslangInvoker;
use Ehimen\Jaslang\FuncDef\BinaryFunction;
use Ehimen\Jaslang\FuncDef\Core\Identity;
use Ehimen\Jaslang\FuncDef\Core\Random;
use Ehimen\Jaslang\FuncDef\Core\Substring;
use Ehimen\Jaslang\FuncDef\Core\Subtract;
use Ehimen\Jaslang\FuncDef\Core\Sum;
use Ehimen\Jaslang\Evaluator\FunctionRepository;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Lexer\DoctrineLexer;
use Ehimen\Jaslang\Parser\JaslangParser;

/**
 * Initialises a Jaslang expression evaluator.
 *
 * This is provided for convenience to bootstrap a default evaluator
 * and its dependencies, offering hooks to configure certain aspects.
 *
 * @see \Ehimen\Jaslang\Evaluator\Evaluator to construct manually if
 *                                          you need more control.
 */
class JaslangFactory
{
    /**
     * @var FunctionRepository
     */
    private $repository;
    
    public function registerFunction($identifier, FuncDef $function)
    {
        // TODO: validate identifier against language.
        $this->getRepository()->registerFunction($identifier, $function);
    }

    public function registerOperator(
        $identifier,
        BinaryFunction $operator,
        $precedence = FunctionRepository::OPERATOR_PRECEDENCE_DEFAULT
    ) {
        // TODO: validate identifier against language.
        $this->getRepository()->registerOperator($identifier, $operator, $precedence);
    }
    
    public function create()
    {
        $repository = $this->getRepository();

        // Core functions.
        $repository->registerFunction('sum', $sum = new Sum());
        $repository->registerFunction('subtract', $sub = new Subtract());
        $repository->registerFunction('substring', new Substring());
        $repository->registerFunction('random', new Random());

        // Core operators.
        $repository->registerOperator('+', $sum);
        $repository->registerOperator('-', $sub);
        $repository->registerOperator('===', new Identity());
        
        $invoker = new JaslangInvoker();
        $parser  = new JaslangParser(new DoctrineLexer($repository->getRegisteredOperatorIdentifiers()));

        $evaluator = new Evaluator($parser, $repository, $invoker);

        // Reset our repository for subsequent create() calls.
        $this->repository = null;

        return $evaluator;
    }

    private function getRepository()
    {
        if (!$this->repository) {
            $this->repository = new FunctionRepository();
        }

        return $this->repository;
    }
}
