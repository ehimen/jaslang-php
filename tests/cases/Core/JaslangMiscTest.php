<?php

namespace Ehimen\JaslangTests\Core;

use Ehimen\Jaslang\Core\FuncDef\Assign;
use Ehimen\Jaslang\Engine\Exception\EvaluationException;
use Ehimen\Jaslang\Engine\Interpreter\Interpreter;
use Ehimen\Jaslang\Engine\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\TypeErrorException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedFunctionException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\UndefinedSymbolException;
use Ehimen\Jaslang\Engine\Evaluator\Trace\EvaluationTrace;
use Ehimen\Jaslang\Engine\Evaluator\Trace\TraceEntry;
use Ehimen\Jaslang\Engine\FuncDef\OperatorSignature;
use Ehimen\Jaslang\Core\JaslangFactory;
use Ehimen\Jaslang\Core\Value\Num;
use Ehimen\Jaslang\Core\Value\Str;
use Ehimen\Jaslang\Engine\Lexer\Lexer;
use Ehimen\Jaslang\Engine\Lexer\Token;
use Ehimen\Jaslang\Engine\Parser\Exception\SyntaxErrorException;
use Ehimen\Jaslang\Engine\Parser\Exception\UnexpectedTokenException;
use Ehimen\JaslangTestResources\AndOperator;
use Ehimen\JaslangTestResources\CustomType\ChildFunction;
use Ehimen\JaslangTestResources\CustomType\ChildType;
use Ehimen\JaslangTestResources\CustomType\ParentType;
use Ehimen\JaslangTestResources\FooFuncDef;
use Ehimen\JaslangTestResources\FooOperator;
use Ehimen\JaslangTestResources\Multiplication;
use PHPUnit\Framework\TestCase;

/**
 * Dumping ground for high-level tests that can't be run through JSLT files.
 */
class JaslangMiscTest extends TestCase
{
    use JaslangTestCase;
    
    public function testRandom()
    {
        $actual = $this->getInterpreter()->run('random()');
        
        $this->assertGreaterThan(0, (int)$actual);
    }

    public function testFunctionOperatorHooks()
    {
        $factory = new JaslangFactory();
        $factory->registerFunction('foo', new FooFuncDef());
        $factory->registerOperator('+-+-+-+-+', new FooOperator(), OperatorSignature::binary());
        
        $result = $factory->create()->run('print("foo" +-+-+-+-+ foo())')->getOut();
        $this->assertSame('true', $result);
    }

    public function testAlphabeticOperator()
    {
        $factory = new JaslangFactory();
        $factory->registerOperator('AND', new AndOperator(), OperatorSignature::binary());
        $evaluator = $factory->create();

        $result = $evaluator->run('print(false AND true)')->getOut();
        $this->assertSame('false', $result);

        $result = $evaluator->run('print(true AND true)')->getOut();
        $this->assertSame('true', $result);
    }

    public function testOperatorPrecedence()
    {
        // TODO: this test is really testing the engine.
        $input = 'print(3 + 5 test-multiply 2)';

        $this->performMultiplicationTest($input, 10, '13');
        $this->performMultiplicationTest($input, -10, '16');
    }

    public function testOperatorPrecedenceComplex()
    {
        // TODO: this test is really testing the engine.
        $input = 'print(3 + sum(3 + 5 test-multiply 2, 2 + 3 test-multiply sum(1, 2)) test-multiply 10)';

        $this->performMultiplicationTest($input, 10, '243');
        $this->performMultiplicationTest($input, -10, '340');
    }

    public function testCustomType()
    {
        $result = $this->getEvaluatorWithCustomType()->run('print(testfunction(c, c))')->getOut();
        
        $this->assertSame('true', $result);
    }

    public function testCustomTypeIsValidated()
    {
        $expected = InvalidArgumentException::invalidArgument('1', 'parenttype', new Num(100));
        $input    = 'testfunction(c, 100)';
        
        $expected->setEvaluationTrace(new EvaluationTrace([
            new TraceEntry('testfunction(c, 100)')
        ]));
        $expected->setInput($input);
        
        $this->performRuntimeExceptionTest(
            $input,
            $expected,
            $this->getEvaluatorWithCustomType()
        );
    }

    private function getEvaluatorWithCustomType()
    {
        $factory = new JaslangFactory();

        $factory->registerType('parenttype', new ParentType());
        $factory->registerType('childtype', new ChildType());
        $factory->registerFunction('testfunction', new ChildFunction());
        
        return $factory->create();
    }

    private function performMultiplicationTest($input, $multiplicationPrecedence, $expected)
    {
        $factory = new JaslangFactory();
        $signature = OperatorSignature::binary($multiplicationPrecedence);
        $factory->registerOperator('test-multiply', new Multiplication(), $signature);
        $this->assertSame($expected, $factory->create()->run($input)->getOut());
    }

    private function performRuntimeExceptionTest($input, RuntimeException $expected, Interpreter $evaluator = null)
    {
        $evaluator = $evaluator ?: $this->getInterpreter();
        
        $result = $evaluator->run($input);
        
        if ($result->getError() instanceof RuntimeException) {
            $this->assertEquals($expected, $result->getError());
        } else {
            $this->fail('A runtime exception was not encountered');
        }
    }
}
