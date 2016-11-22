<?php

namespace Ehimen\JaslangTests\Core;

use Ehimen\Jaslang\Core\FuncDef\Assign;
use Ehimen\Jaslang\Engine\Exception\EvaluationException;
use Ehimen\Jaslang\Engine\Interpreter;
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
 * High-level tests for Jaslang evaluation.
 * 
 * Runs test files (.jslt) in resources/jsltests
 * 
 * TODO: remove left-over tests that couldn't be moved to jslt.
 */
class JaslangTest extends TestCase
{
    const TESTDIR = __DIR__ . '/../../resources/jsltests';
    const TESTFILEREGEX = '#\.jslt$#';

    /**
     * @dataProvider provideFiles
     */
    public function testFile($expected, $code, $error)
    {
        try {
            $this->assertSame($expected, $this->getInterpreter()->run($code));
        } catch (EvaluationException $e) {
            if (is_string($error)) {
                $this->assertSame(trim($error), trim((string)$e));
                return;
            } else {
                throw $e;
            }
        }

        if (is_string($error)) {
            $this->fail('Expected Jaslang error, but one was not thrown');
        }
    }

    public function provideFiles()
    {
        $files = $this->getTestFiles();
        $cases = [];

        foreach ($files as $file) {
            $contents = file_get_contents($file);

            $tests = preg_split('/!>(\S+)\n/', $contents, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

            if (1 === count($tests)) {
                $tests = ['', reset($tests)];
            }

            for ($i = 0; $i < count($tests); $i += 2) {
                $case    = isset($tests[$i]) ? $tests[$i] : null;
                $content = isset($tests[$i + 1]) ? $tests[$i + 1] : null;

                if (!is_string($content) || !is_string($case)) {
                    throw new \Exception('Invalid jsl test file contents in file: ' . $file);
                }

                $parts = preg_split('/>>>EXPECTED\n|\n?>>>CODE\n|\n?>>>ERROR\n/', $content);

                $expected = isset($parts[1]) ? $parts[1] : null;
                $code     = isset($parts[2]) ? $parts[2] : null;
                $error    = isset($parts[3]) ? $parts[3] : null;

                if (!is_string($expected) || !is_string($code)) {
                    throw new \Exception('Invalid jsl test file contents in file: ' . $file);
                }

                $cases[$file . '#' . $case] = [$expected, $code, $error];
            }
        }

        return $cases;
    }
    
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
        
        $result = $factory->create()->run('"foo" +-+-+-+-+ foo()');
        $this->assertSame('true', $result);
    }

    public function testAlphabeticOperator()
    {
        $factory = new JaslangFactory();
        $factory->registerOperator('AND', new AndOperator(), OperatorSignature::binary());
        $evaluator = $factory->create();

        $result = $evaluator->run('false AND true');
        $this->assertSame('false', $result);

        $result = $evaluator->run('true AND true');
        $this->assertSame('true', $result);
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

    public function testCustomType()
    {
        $result = $this->getEvaluatorWithCustomType()->run('testfunction(c, c)');
        
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

    public function testVariableInitialisation()
    {
        $this->markTestSkipped('Remove this once we have removed implicit returns.');
        
        $this->performTest(
            'let string foo',
            '[variable] foo'
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
        $actual = $this->getInterpreter()->run($input);

        $this->assertSame($expected, $actual);
    }

    private function performMultiplicationTest($input, $multiplicationPrecedence, $expected)
    {
        $factory = new JaslangFactory();
        $signature = OperatorSignature::binary($multiplicationPrecedence);
        $factory->registerOperator('test-multiply', new Multiplication(), $signature);
        $this->assertSame($expected, $factory->create()->run($input));
    }

    private function performRuntimeExceptionTest($input, RuntimeException $expected, Interpreter $evaluator = null)
    {
        $evaluator = $evaluator ?: $this->getInterpreter();
        
        try {
            $evaluator->run($input);
        } catch (RuntimeException $actual) {
            $this->assertEquals($expected, $actual);
            return;
        }
        
        $this->fail('A runtime exception was not thrown');
    }
    
    private function getInterpreter()
    {
        return (new JaslangFactory())->create();
    }

    private function getTestFiles()
    {
        $directory    = new \RecursiveDirectoryIterator(static::TESTDIR);
        $iterator     = new \RecursiveIteratorIterator($directory);
        $fileIterator = new \RegexIterator($iterator, static::TESTFILEREGEX);

        return array_map(
            function (\SplFileInfo $fileInfo) {
                return $fileInfo->getRealPath();
            },
            iterator_to_array($fileIterator)
        );
    }
}
