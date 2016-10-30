<?php

namespace Ehimen\JaslangTests;

use Ehimen\Jaslang\Ast\FunctionCall;
use Ehimen\Jaslang\Evaluator\Evaluator;
use Ehimen\Jaslang\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Evaluator\Exception\UndefinedFunctionException;
use Ehimen\Jaslang\Evaluator\Exception\UndefinedOperatorException;
use Ehimen\Jaslang\Evaluator\Trace\EvaluationTrace;
use Ehimen\Jaslang\Evaluator\Trace\TraceEntry;
use Ehimen\Jaslang\JaslangFactory;
use Ehimen\Jaslang\Value\Num;
use Ehimen\Jaslang\Value\Str;
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
        $expected = new InvalidArgumentException(0, 'number');
        
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
        $expected = new InvalidArgumentException(1, 'number');
        
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
        $expected = new InvalidArgumentException(0, 'number', new Str("foo"));
        
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
        
        $expected->setInput('sum(sum(1, 3), rand(sum(4, 3), definitelynotacorefunction("100")))');
        $expected->setEvaluationTrace(new EvaluationTrace([
            new TraceEntry('sum(sum(1, 3), rand(sum(4, 3), definitelynotacorefunction("100")))'),
            new TraceEntry('rand(sum(4, 3), definitelynotacorefunction("100"))'),
            new TraceEntry('definitelynotacorefunction("100")'),
        ]));
        
        $this->performRuntimeExceptionTest(
            'sum(sum(1, 3), rand(sum(4, 3), definitelynotacorefunction("100")))',
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
        $factory->registerOperator('+-+-+-+-+', new FooOperator());
        
        $result = $factory->create()->evaluate('"foo" +-+-+-+-+ foo()');
        $this->assertSame('true', $result);
    }

    public function testAlphabeticOperator()
    {
        $factory = new JaslangFactory();
        $factory->registerOperator('AND', new AndOperator());
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
        $input = '3 + 5 * 2';

        $this->performMultiplicationTest($input, 10, '13');
        $this->performMultiplicationTest($input, -10, '16');
    }

    public function testOperatorPrecedenceComplex()
    {
        $input = '3 + sum(3 + 5 * 2, 2 + 3 * sum(1, 2)) * 10';

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
        $expected = new InvalidArgumentException('1', 'parenttype', new Num(100));
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
    
    private function performTest($input, $expected)
    {
        $actual = $this->getEvaluator()->evaluate($input);

        $this->assertSame($expected, $actual);
    }

    private function performMultiplicationTest($input, $multiplicationPrecedence, $expected)
    {
        $factory = new JaslangFactory();
        $factory->registerOperator('*', new Multiplication(), $multiplicationPrecedence);
        $this->assertSame($expected, $factory->create()->evaluate($input));
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
