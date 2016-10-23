<?php

namespace Ehimen\JaslangTests;

use Ehimen\Jaslang\JaslangFactory;
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
    
    private function performTest($input, $expected)
    {
        $actual = $this->getEvaluator()->evaluate($input);
        
        $this->assertSame($expected, $actual);
    }
    
    private function getEvaluator()
    {
        return (new JaslangFactory())->createDefault();
    }
}