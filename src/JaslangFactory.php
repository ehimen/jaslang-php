<?php

namespace Ehimen\Jaslang;

use Ehimen\Jaslang\Evaluator\Evaluator;
use Ehimen\Jaslang\Evaluator\JaslangInvoker;
use Ehimen\Jaslang\FuncDef\OperatorSignature;
use Ehimen\Jaslang\Type\TypeRepository;
use Ehimen\Jaslang\FuncDef\BinaryFunction;
use Ehimen\Jaslang\FuncDef\Core\Identity;
use Ehimen\Jaslang\FuncDef\Core\Random;
use Ehimen\Jaslang\FuncDef\Core\Substring;
use Ehimen\Jaslang\FuncDef\Core\Subtract;
use Ehimen\Jaslang\FuncDef\Core\Sum;
use Ehimen\Jaslang\FuncDef\FunctionRepository;
use Ehimen\Jaslang\FuncDef\FuncDef;
use Ehimen\Jaslang\Lexer\JaslangLexer;
use Ehimen\Jaslang\Parser\JaslangParser;
use Ehimen\Jaslang\Type\Core\Any;
use Ehimen\Jaslang\Type\Core\Boolean;
use Ehimen\Jaslang\Type\Core\Num;
use Ehimen\Jaslang\Type\Core\Str;
use Ehimen\Jaslang\Type\Type;

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
    private $functionRepository;

    /**
     * @var TypeRepository
     */
    private $typeRepository;
    
    public function registerFunction($identifier, FuncDef $function)
    {
        // TODO: validate identifier against language.
        $this->getFunctionRepository()->registerFunction($identifier, $function);
    }

    public function registerOperator(
        $identifier,
        BinaryFunction $operator,
        OperatorSignature $signature
    ) {
        // TODO: validate identifier against language.
        $this->getFunctionRepository()->registerOperator($identifier, $operator, $signature);
    }

    public function registerType($name, Type $type)
    {
        $this->getTypeRepository()->registerType($name, $type);
    }
    
    public function create()
    {
        $fnRepo = $this->getFunctionRepository();
        $typeRepo = $this->getTypeRepository();

        // Core functions.
        $fnRepo->registerFunction('sum', $sum = new Sum());
        $fnRepo->registerFunction('subtract', $sub = new Subtract());
        $fnRepo->registerFunction('substring', new Substring());
        $fnRepo->registerFunction('random', new Random());

        // Core operators.
        $fnRepo->registerOperator('+', $sum, OperatorSignature::binaryOperator());
        $fnRepo->registerOperator('-', $sub, OperatorSignature::binaryOperator());
        $fnRepo->registerOperator('===', new Identity(), OperatorSignature::binaryOperator());
        
        $typeRepo->registerType('any', new Any());
        $typeRepo->registerType('string', new Str());
        $typeRepo->registerType('number', new Num());
        $typeRepo->registerType('boolean', new Boolean());
        
        $invoker = new JaslangInvoker($typeRepo);
        $parser  = new JaslangParser(
            new JaslangLexer(
                $fnRepo->getRegisteredOperatorIdentifiers(),
                $typeRepo->getConcreteTypeLiteralPatterns()
            ),
            $fnRepo,
            $typeRepo
        );

        $evaluator = new Evaluator($parser, $fnRepo, $invoker);

        // Reset our repository for subsequent create() calls.
        $this->functionRepository = null;

        return $evaluator;
    }

    private function getFunctionRepository()
    {
        if (!$this->functionRepository) {
            $this->functionRepository = new FunctionRepository();
        }

        return $this->functionRepository;
    }

    private function getTypeRepository()
    {
        if (!$this->typeRepository) {
            $this->typeRepository = new TypeRepository();
        }
        
        return $this->typeRepository;
    }
}
