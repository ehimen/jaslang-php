<?php

namespace Ehimen\JaslangTests\Core;

use Ehimen\Jaslang\Core\FuncDef\Assign;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
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
 * High-level tests for Jaslang evaluation.
 */
class JaslangTest extends TestCase
{
    public function testStringLiteral()
    {
        $this->performTest('"foo"', 'foo');
    }
    
    public function testNumberLiteral()
    {
        $this->performTest('13', '13');
    }
    
    public function testSum()
    {
        $this->performTest('sum(1, 3)', '4');
    }
    
    public function testSubtract()
    {
        $this->performTest('subtract(15, 2)', '13');
    }
    
    public function testSimpleArithmetic()
    {
        $this->performTest('sum(subtract(10, 4), sum(9, subtract(3, 1)))', '17');
    }
    
    public function testSubstring()
    {
        $this->performTest('substring("hello world", 2, 3)', 'llo');
    }

    public function testMultiline()
    {
        $input = <<<JASLANG
sum(
    sum(
        sum(
            sum(
                sum(
                    sum(
                        sum(
                            sum(
                                sum(
                                    sum(
                                        1,
                                        1
                                    ),
                                    1
                                ),
                                1
                            ),
                            1
                        ),
                        1
                    ),
                    1
                ),
                1
            ),
            1
        ),
        1
    ),
    2
)
JASLANG;
        
        $this->performTest($input, '12');
    }

    public function testRandom()
    {
        $actual = $this->getEvaluator()->evaluate('random()');
        
        $this->assertGreaterThan(0, (int)$actual);
    }

    public function testAddition()
    {
        $this->performTest('subtract(13 + 24, 7 + 5)', '25');
    }

    public function testChainedAddition()
    {
        $this->performTest('1 + 2 + 3', '6');
    }

    /**
     * TODO: ideally move this to a dedicated evaluator test.
     */
    public function testSubtractNoArgs()
    {
        $expected = InvalidArgumentException::invalidArgument(0, 'number');
        
        $expected->setInput('subtract()');
        $expected->setEvaluationTrace(new EvaluationTrace([
            new TraceEntry('subtract()')
        ]));
        
        $this->performRuntimeExceptionTest(
            'subtract()',
            $expected
        );
    }

    /**
     * TODO: ideally move this to a dedicated evaluator test.
     */
    public function testSubtractOneArg()
    {
        $expected = InvalidArgumentException::invalidArgument(1, 'number');
        
        $expected->setInput('subtract(100)');
        $expected->setEvaluationTrace(new EvaluationTrace([
            new TraceEntry('subtract(100)')
        ]));
        
        $this->performRuntimeExceptionTest(
            'subtract(100)',
            $expected
        );
    }

    /**
     * TODO: ideally move this to a dedicated evaluator test.
     */
    public function testSubtractNestedInvalidArg()
    {
        $expected = InvalidArgumentException::invalidArgument(0, 'number', new Str("foo"));
        
        $expected->setInput('sum(sum(1, 3), sum(1 + 3, subtract("foo")))');
        $expected->setEvaluationTrace(new EvaluationTrace([
            new TraceEntry('sum(sum(1, 3), sum(1 + 3, subtract("foo")))'),
            new TraceEntry('sum(1 + 3, subtract("foo"))'),
            new TraceEntry('subtract("foo")'),
        ]));
        
        $this->performRuntimeExceptionTest(
            'sum(sum(1, 3), sum(1 + 3, subtract("foo")))',
            $expected
        );
    }

    /**
     * TODO: ideally move this to a dedicated evaluator test.
     */
    public function testUndefinedFunction()
    {
        $expected = new UndefinedFunctionException('definitelynotacorefunction');
        
        $expected->setInput('sum(sum(1, 3), random(sum(4, 3), definitelynotacorefunction("100")))');
        $expected->setEvaluationTrace(new EvaluationTrace([
            new TraceEntry('sum(sum(1, 3), random(sum(4, 3), definitelynotacorefunction("100")))'),
            new TraceEntry('random(sum(4, 3), definitelynotacorefunction("100"))'),
            new TraceEntry('definitelynotacorefunction("100")'),
        ]));
        
        $this->performRuntimeExceptionTest(
            'sum(sum(1, 3), random(sum(4, 3), definitelynotacorefunction("100")))',
            $expected
        );
    }

    public function testSubtractOperator()
    {
        $this->performTest('3 - 4', '-1');
    }

    public function testSignedNumbers()
    {
        $this->performTest('-3.5 - +4', '-7.5');
    }

    public function getIdentityIdentical()
    {
        $this->performTest('1 === 1', 'true');
        $this->performTest('"foo" === "foo"', 'true');
        $this->performTest('false === false', 'true');
    }

    public function testIdentityDifferent()
    {
        $this->performTest('1 === "1"', 'false');
        $this->performTest('"foo" === "bar"', 'false');
        $this->performTest('false === true', 'false');
    }

    public function testFunctionOperatorHooks()
    {
        $factory = new JaslangFactory();
        $factory->registerFunction('foo', new FooFuncDef());
        $factory->registerOperator('+-+-+-+-+', new FooOperator(), OperatorSignature::binary());
        
        $result = $factory->create()->evaluate('"foo" +-+-+-+-+ foo()');
        $this->assertSame('true', $result);
    }

    public function testAlphabeticOperator()
    {
        $factory = new JaslangFactory();
        $factory->registerOperator('AND', new AndOperator(), OperatorSignature::binary());
        $evaluator = $factory->create();

        $result = $evaluator->evaluate('false AND true');
        $this->assertSame('false', $result);

        $result = $evaluator->evaluate('true AND true');
        $this->assertSame('true', $result);
    }

    public function testComplexFunctionOperatorNesting()
    {
        $this->performTest(
            '1 - 2 + sum(sum(3, 4) + 5, 6 + 7 - sum(8, 9) + sum(10, 11)) + 12 + 13',
            '53'
        );
    }

    public function testParenGroupPrecedence()
    {
        $this->performTest('3 - 1 + 2 + sum(7 - 2 + 10, 5)', '24');
        $this->performTest('3 - ((1 + 2) + sum(7 - (2 + 10), 5))', '0');
    }

    public function testOperatorPrecedence()
    {
        // TODO: this test is really testing the engine.
        $input = '3 + 5 test-multiply 2';

        $this->performMultiplicationTest($input, 10, '13');
        $this->performMultiplicationTest($input, -10, '16');
    }

    public function testOperatorPrecedenceComplex()
    {
        // TODO: this test is really testing the engine.
        $input = '3 + sum(3 + 5 test-multiply 2, 2 + 3 test-multiply sum(1, 2)) test-multiply 10';

        $this->performMultiplicationTest($input, 10, '243');
        $this->performMultiplicationTest($input, -10, '340');
    }

    public function testMultiStatement()
    {
        $this->performTest("sum(1, 2); sum(4, 5)", '9');
    }

    public function testCustomType()
    {
        $result = $this->getEvaluatorWithCustomType()->evaluate('testfunction(c, c)');
        
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

    public function testBooleanCaseSensitivity()
    {
        $this->performTest('True', 'true');
        $this->performTest('TRUE', 'true');
        $this->performTest('true', 'true');
        $this->performTest('False', 'false');
        $this->performTest('FALSE', 'false');
        $this->performTest('True', 'true');
    }

    public function testVariableInitialisation()
    {
        $this->performTest(
            'let string foo',
            '[variable] foo'
        );
    }

    public function testVariableAssignment()
    {
        $this->performTest(
            'let string foo = "bar"',
            'bar'
        );
    }

    public function testVariablesInFunction()
    {
        $code = <<<CODE
let number one = 13;
let number two = 24;

sum(one, two)
CODE;
        
        $this->performTest($code, '37');
    }

    public function testSeparateAssignment()
    {
        $code = <<<CODE
let number one;
let number two;

one = 13;
two = 24;

sum(one, two)
CODE;
        
        $this->performTest($code, '37');
    }

    public function testAssignmentTypeMismatchThrows()
    {
        $input = 'let number notnumber = "13"';
        
        $exception = Assign::typeMismatch('number', new Str('13'));
        
        $exception->setEvaluationTrace(new EvaluationTrace([
            new TraceEntry('let number notnumber = "13"'),
        ]));
        $exception->setInput($input);
        
        $this->performRuntimeExceptionTest(
            $input,
            $exception
        );
    }

    public function testUndefinedVariableThrows()
    {
        $input = 'let string foo = "bar"; substring(bar, 0, 1)';
        
        $exception = new UndefinedSymbolException('bar');
        $exception->setInput($input);
        $exception->setEvaluationTrace(new EvaluationTrace([
            new TraceEntry('substring(bar, 0, 1)'),
        ]));
        
        $this->performRuntimeExceptionTest($input, $exception);
    }

    public function testRepeatedIdentiifers()
    {
        $input = 'foo bar';
        
        $expected = new UnexpectedTokenException('foo bar', new Token('bar', Lexer::TOKEN_IDENTIFIER, 5));
        
        $this->performSyntaxErrorTest($input, $expected);
    }

    public function testRepeatedLiterals()
    {
        $input = '"foo" 1337';
        
        $expected = new UnexpectedTokenException('"foo" 1337', new Token('1337', Lexer::TOKEN_LITERAL, 7));
        
        $this->performSyntaxErrorTest($input, $expected);
    }

    private function getEvaluatorWithCustomType()
    {
        $factory = new JaslangFactory();

        $factory->registerType('parenttype', new ParentType());
        $factory->registerType('childtype', new ChildType());
        $factory->registerFunction('testfunction', new ChildFunction());
        
        return $factory->create();
    }
    
    private function performTest($input, $expected)
    {
        $actual = $this->getEvaluator()->evaluate($input);

        $this->assertSame($expected, $actual);
    }

    private function performMultiplicationTest($input, $multiplicationPrecedence, $expected)
    {
        $factory = new JaslangFactory();
        $signature = OperatorSignature::binary($multiplicationPrecedence);
        $factory->registerOperator('test-multiply', new Multiplication(), $signature);
        $this->assertSame($expected, $factory->create()->evaluate($input));
    }

    private function performSyntaxErrorTest($input, SyntaxErrorException $expected)
    {
        try {
            $this->getEvaluator()->evaluate($input);
        } catch (SyntaxErrorException $actual) {
            $this->assertEquals($expected, $actual);
            return;
        }

        $this->fail('A syntax error was not thrown');
    }

    private function performRuntimeExceptionTest($input, RuntimeException $expected, Evaluator $evaluator = null)
    {
        $evaluator = $evaluator ?: $this->getEvaluator();
        
        try {
            $evaluator->evaluate($input);
        } catch (RuntimeException $actual) {
            $this->assertEquals($expected, $actual);
            return;
        }
        
        $this->fail('A runtime exception was not thrown');
    }
    
    private function getEvaluator()
    {
        return (new JaslangFactory())->create();
    }
}
