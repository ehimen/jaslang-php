<?php

namespace Ehimen\JaslangTests;

use Ehimen\Jaslang\Ast\FunctionCall;
use Ehimen\Jaslang\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Evaluator\Exception\UndefinedFunctionException;
use Ehimen\Jaslang\Evaluator\Exception\UndefinedOperatorException;
use Ehimen\Jaslang\Evaluator\Trace\EvaluationTrace;
use Ehimen\Jaslang\Evaluator\Trace\TraceEntry;
use Ehimen\Jaslang\JaslangFactory;
use Ehimen\Jaslang\Value\Str;
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
    public function testUndefinedOperator()
    {
        $expected = new UndefinedOperatorException('**^^==');
        
        $expected->setInput('sum(sum(1, 3), sum(1 **^^== 3))');
        $expected->setEvaluationTrace(new EvaluationTrace([
            new TraceEntry('sum(sum(1, 3), sum(1 **^^== 3))'),
            new TraceEntry('sum(1 **^^== 3)'),
            new TraceEntry('1 **^^== 3'),
        ]));
        
        $this->performRuntimeExceptionTest(
            'sum(sum(1, 3), sum(1 **^^== 3))',
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
    
    private function performTest($input, $expected)
    {
        $actual = $this->getEvaluator()->evaluate($input);
        
        $this->assertSame($expected, $actual);
    }

    private function performRuntimeExceptionTest($input, RuntimeException $expected)
    {
        try {
            $this->getEvaluator()->evaluate($input);
        } catch (RuntimeException $actual) {
            $this->assertEquals($expected, $actual);
            return;
        }
        
        $this->fail('A runtime exception was not thrown');
    }
    
    private function getEvaluator()
    {
        return (new JaslangFactory())->createDefault();
    }
}