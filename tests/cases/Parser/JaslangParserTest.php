<?php

namespace Ehimen\JaslangTests\Parser;

use Ehimen\Jaslang\Ast\BinaryOperation\AdditionOperation;
use Ehimen\Jaslang\Ast\FunctionCall;
use Ehimen\Jaslang\Ast\Node;
use Ehimen\Jaslang\Ast\NumberLiteral;
use Ehimen\Jaslang\Ast\StringLiteral;
use Ehimen\Jaslang\Lexer\Lexer;
use Ehimen\Jaslang\Parser\Exception\SyntaxErrorException;
use Ehimen\Jaslang\Parser\Exception\UnexpectedEndOfInputException;
use Ehimen\Jaslang\Parser\Exception\UnexpectedTokenException;
use Ehimen\Jaslang\Parser\JaslangParser;
use Ehimen\JaslangTests\JaslangTestUtil;
use PHPUnit\Framework\TestCase;

class JaslangParserTest extends TestCase 
{
    use JaslangTestUtil;
    
    public function testFunctionCallNoArgs()
    {
        $this->performTest(
            'foo()',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 5),
            ],
            new FunctionCall('foo', [])
        );
    }
    
    public function testFunctionCallStringArg()
    {
        $this->performTest(
            'foo("bar")',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
                $this->createToken(Lexer::TOKEN_STRING, 'bar', 5),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 10),
            ],
            new FunctionCall(
                'foo',
                [new StringLiteral('bar')]
            )
        );
    }
    
    public function testFunctionCallStringArgs()
    {
        $this->performTest(
            'foo("bar", "baz")',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
                $this->createToken(Lexer::TOKEN_STRING, 'bar', 5),
                $this->createToken(Lexer::TOKEN_COMMA, ',', 10),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 11),
                $this->createToken(Lexer::TOKEN_STRING, 'baz', 12),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 17),
            ],
            new FunctionCall(
                'foo',
                [new StringLiteral('bar'), new StringLiteral('baz')]
            )
        );
    }
    
    public function testNestedFunctionCall()
    {
        $this->performTest(
            'foo(bar())',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'bar', 5),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 8),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 9),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 10)
            ],
            new FunctionCall(
                'foo',
                [new FunctionCall('bar', [])]
            )
        );
    }
    
    public function testNestedMultiFunctionCall()
    {
        $this->performTest(
            'foo(bar(), baz())',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'bar', 5),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 8),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 9),
                $this->createToken(Lexer::TOKEN_COMMA, ',', 10),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 11),
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'baz', 12),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 15),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 16),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 17)
            ],
            new FunctionCall(
                'foo',
                [new FunctionCall('bar', []), new FunctionCall('baz', [])]
            )
        );
    }
    
    public function testNestedWhitespaceFunctionCall()
    {
        $this->performTest(
            'foo ( bar( )  ,  baz( ) )',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 4),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 5),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 6),
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'bar', 7),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 8),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 9),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 10),
                $this->createToken(Lexer::TOKEN_WHITESPACE, '  ', 11),
                $this->createToken(Lexer::TOKEN_COMMA, ',', 13),
                $this->createToken(Lexer::TOKEN_WHITESPACE, '  ', 14),
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'baz', 16),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 19),
                $this->createToken(Lexer::TOKEN_WHITESPACE, '  ', 20),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 22),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 23),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 24)
            ],
            new FunctionCall(
                'foo',
                [new FunctionCall('bar', []), new FunctionCall('baz', [])]
            )
        );
    }

    public function testStringLiteral()
    {
        $this->performTest(
            '"foo"',
            [$this->createToken(Lexer::TOKEN_STRING, 'foo', 1)],
            new StringLiteral('foo')
        );
    }

    public function testNumberLiteral()
    {
        $this->performTest(
            '3.14',
            [$this->createToken(Lexer::TOKEN_NUMBER, '3.14', 1)],
            new NumberLiteral(3.14)
        );
    }

    public function testLeadingTrailingWhitespace()
    {
        $this->performTest(
            '  1337   ',
            [
                $this->createToken(Lexer::TOKEN_WHITESPACE, '  ', 1),
                $this->createToken(Lexer::TOKEN_NUMBER, '1337', 3),
                $this->createToken(Lexer::TOKEN_WHITESPACE, '   ', 6),
            ],
            new NumberLiteral(1337)
        );
    }

    public function testRepeatedString()
    {
        $this->performSyntaxErrorTest(
            '"foo" "bar"',
            [
                $this->createToken(Lexer::TOKEN_STRING, 'foo', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 5),
                $unexpected = $this->createToken(Lexer::TOKEN_STRING, 'bar', 6),
            ],
            $this->unexpectedTokenException('"foo" "bar"', $unexpected)
        );
    }

    public function testRepeatedLiterals()
    {
        $this->performSyntaxErrorTest(
            '"foo" 1337',
            [
                $this->createToken(Lexer::TOKEN_STRING, 'foo', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 5),
                $unexpected = $this->createToken(Lexer::TOKEN_NUMBER, '1337', 6),
            ],
            $this->unexpectedTokenException('"foo" 1337', $unexpected)
        );
    }

    public function testOpenParen()
    {
        $this->performSyntaxErrorTest(
            '(',
            [
                $unexpected = $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 1),
            ],
            $this->unexpectedTokenException('(', $unexpected)
        );
    }

    public function testCloseParen()
    {
        $this->performSyntaxErrorTest(
            ')',
            [
                $unexpected = $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 1),
            ],
            $this->unexpectedTokenException(')', $unexpected)
        );
    }

    public function testAdditionOperator()
    {
        $this->performTest(
            '3 + 4',
            [
                $this->createToken(Lexer::TOKEN_NUMBER, '3', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 2),
                $this->createToken(Lexer::TOKEN_PLUS, '+', 3),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 4),
                $this->createToken(Lexer::TOKEN_NUMBER, '4', 5)
            ],
            new AdditionOperation(
                new NumberLiteral(3),
                new NumberLiteral(4)
            )
        );
    }

    public function testMissingComma()
    {
        $this->performSyntaxErrorTest(
            'foo("foo" "bar")',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
                $this->createToken(Lexer::TOKEN_STRING, 'foo', 5),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 10),
                $unexpected = $this->createToken(Lexer::TOKEN_STRING, 'bar', 11),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 16),
            ],
            $this->unexpectedTokenException('foo("foo" "bar")', $unexpected)
        );
    }

    public function testNonTerminatedParens()
    {
        $this->performSyntaxErrorTest(
            'foo(',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
            ],
            $this->unexpectedEndOfInputException('foo(')
        );
    }

    public function testOverTerminatingParens()
    {
        $this->performSyntaxErrorTest(
            'foo())',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 5),
                $unexpected = $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 6),
            ],
            $this->unexpectedTokenException('foo())', $unexpected)
        );
    }
    
    // TODO: test + on strings?

    public function testRogueBackslash()
    {
        $this->performSyntaxErrorTest(
            'foo\bar',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
                $unexpected = $this->createToken(Lexer::TOKEN_BACKSLASH, '\\', 4),
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'bar', 5),
            ],
            $this->unexpectedTokenException('foo\bar', $unexpected)
        );
    }

    public function testUnknownToken()
    {
        $this->performSyntaxErrorTest(
            'foo(@)',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
                $unexpected = $this->createToken(Lexer::TOKEN_UNKNOWN, '@', 5),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 6),
            ],
            $this->unexpectedTokenException('foo(@)', $unexpected)
        );
    }

    private function performSyntaxErrorTest($input, array $tokens, $expected)
    {
        $parser = $this->getParser($this->getLexer($input, $tokens));
            
        try {
            $parser->parse($input);
        } catch (SyntaxErrorException $e) {
            $this->assertEquals($expected, $e);
            return;
        }
        
        $this->fail('Test did not raise a syntax error');
    }

    private function performTest($input, $tokens, Node $expected)
    {
        $actual = $this->getParser($this->getLexer($input, $tokens))->parse($input);

        $this->assertEquals($expected, $actual);
    }

    private function getLexer($input, array $tokens)
    {
        $lexer = $this->createMock(Lexer::class);

        $lexer->method('tokenize')
            ->with($input)
            ->willReturn($tokens);
        
        return $lexer;
    }

    private function unexpectedTokenException($input, $token)
    {
        return new UnexpectedTokenException($input, $token);
    }

    private function unexpectedEndOfInputException($input)
    {
        return new UnexpectedEndOfInputException($input);
    }

    private function getParser(Lexer $lexer)
    {
        return new JaslangParser($lexer);
    }
}