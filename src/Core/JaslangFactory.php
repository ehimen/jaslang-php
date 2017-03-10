<?php

namespace Ehimen\Jaslang\Core;

use Ehimen\Jaslang\Core\FuncDef\ArrayAccess;
use Ehimen\Jaslang\Core\FuncDef\Assign;
use Ehimen\Jaslang\Core\FuncDef\Concatenate;
use Ehimen\Jaslang\Core\FuncDef\GreaterThan;
use Ehimen\Jaslang\Core\FuncDef\IfDef;
use Ehimen\Jaslang\Core\FuncDef\Increment;
use Ehimen\Jaslang\Core\FuncDef\Lambda;
use Ehimen\Jaslang\Core\FuncDef\LessThan;
use Ehimen\Jaslang\Core\FuncDef\Let;
use Ehimen\Jaslang\Core\FuncDef\Multiply;
use Ehimen\Jaslang\Core\FuncDef\Negate;
use Ehimen\Jaslang\Core\FuncDef\PrintDef;
use Ehimen\Jaslang\Core\FuncDef\PrintLine;
use Ehimen\Jaslang\Core\FuncDef\ReturnVal;
use Ehimen\Jaslang\Core\FuncDef\VariableWithType;
use Ehimen\Jaslang\Core\FuncDef\WhileDef;
use Ehimen\Jaslang\Engine\Evaluator\Context\JaslangContextFactory;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\FuncDef\ListOperatorSignature;
use Ehimen\Jaslang\Engine\Interpreter\Interpreter;
use Ehimen\Jaslang\Engine\Evaluator\JaslangInvoker;
use Ehimen\Jaslang\Engine\FuncDef\OperatorSignature;
use Ehimen\Jaslang\Engine\Parser\Validator\JaslangValidator;
use Ehimen\Jaslang\Engine\Type\TypeRepository;
use Ehimen\Jaslang\Engine\FuncDef\BinaryFunction;
use Ehimen\Jaslang\Core\FuncDef\Equality;
use Ehimen\Jaslang\Core\FuncDef\Random;
use Ehimen\Jaslang\Core\FuncDef\Substring;
use Ehimen\Jaslang\Core\FuncDef\Subtract;
use Ehimen\Jaslang\Core\FuncDef\Sum;
use Ehimen\Jaslang\Engine\FuncDef\FunctionRepository;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\Lexer\JaslangLexer;
use Ehimen\Jaslang\Engine\Parser\JaslangParser;
use Ehimen\Jaslang\Core\Type as TypeDef;
use Ehimen\Jaslang\Engine\Type\Type;

/**
 * Initialises a Jaslang expression evaluator.
 *
 * This is provided for convenience to bootstrap a default evaluator
 * and its dependencies, offering hooks to configure certain aspects.
 *
 * @see \Ehimen\Jaslang\Engine\Interpreter\Interpreter to construct manually if
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
        $fnRepo->registerFunction('print', new PrintDef());
        $fnRepo->registerFunction('println', new PrintLine());

        // Core operators.
        $fnRepo->registerOperator('=>', new Lambda(), OperatorSignature::binary(75));      // Higher than assignment (should be evaluated before assignment; lower in the AST).
        $fnRepo->registerOperator('++', new Increment(), OperatorSignature::arbitrary(1, 0, 75)); // Higher than assignment.
        $fnRepo->registerOperator('+', $sum, OperatorSignature::binary());
        $fnRepo->registerOperator('-', $sub, OperatorSignature::binary());
        $fnRepo->registerOperator('==', new Equality(), OperatorSignature::binary());
        $fnRepo->registerOperator('let', new Let(), OperatorSignature::arbitrary(0, 1, 100));
        $fnRepo->registerOperator('=', new Assign(), OperatorSignature::binary(50));
        $fnRepo->registerOperator('*', new Multiply(), OperatorSignature::binary());
        $fnRepo->registerOperator('!', new Negate(), OperatorSignature::arbitrary(0, 1));
        $fnRepo->registerOperator('if', new IfDef(), OperatorSignature::arbitrary(0, 2));
        $fnRepo->registerOperator('while', new WhileDef(), OperatorSignature::arbitrary(0, 2));
        $fnRepo->registerOperator('<', new LessThan(), OperatorSignature::binary());
        $fnRepo->registerOperator('>', new GreaterThan(), OperatorSignature::binary());
        $fnRepo->registerOperator(':', new VariableWithType(), OperatorSignature::arbitrary(1, 1, 150));
        $fnRepo->registerOperator('return', new ReturnVal(), OperatorSignature::arbitrary(0, 1, -10));     // Low priority; this is the last thing that should be evaluated (higher in the AST).
        $fnRepo->registerOperator('.', new Concatenate(), OperatorSignature::binary());
        
        // List operations.
        $fnRepo->registerListOperation(new ArrayAccess(), ListOperatorSignature::create(
            '[',
            ']',
            1,
            0,
            200     // Higher than var-type binding (:)
        ));
        
        $typeRepo->registerType('any', new TypeDef\Any());
        $typeRepo->registerType('string', new TypeDef\Str());
        $typeRepo->registerType('number', new TypeDef\Num());
        $typeRepo->registerType('boolean', new TypeDef\Boolean());
        $typeRepo->registerType('lambda', new TypeDef\Lambda());

        $contextFactory = new JaslangContextFactory($typeRepo);
        $invoker        = new JaslangInvoker($typeRepo);
        $parser         = new JaslangParser(
            new JaslangLexer(
                $fnRepo->getRegisteredOperatorIdentifiers(),
                $typeRepo->getConcreteTypeLiteralPatterns()
            ),
            $fnRepo,
            $typeRepo,
            $validator = new JaslangValidator()
        );
        
        $parser->registerNodeCreationObserver($validator);

        $evaluator = new Interpreter(
            $parser,
            new Evaluator($invoker, $fnRepo, $contextFactory)
        );

        // Reset our repository for subsequent create() calls.
        $this->functionRepository = null;
        $this->typeRepository     = null;

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
