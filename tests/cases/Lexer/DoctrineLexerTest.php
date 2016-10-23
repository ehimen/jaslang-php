<?php

namespace Ehimen\JaslangTests\Lexer;

use Ehimen\Jaslang\Lexer\DoctrineLexer;
use Ehimen\Jaslang\Lexer\Lexer;
use Ehimen\Jaslang\Parser\Exception\SyntaxErrorException;
use Ehimen\Jaslang\Parser\Exception\UnexpectedEndOfInputException;
use Ehimen\JaslangTests\JaslangTestUtil;
use PHPUnit\Framework\TestCase;

class DoctrineLexerTest extends TestCase
{
    use JaslangTestUtil;

    public function testUnquoted()
    {
        $this->performTest(
            'foo',
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1)
        );
    }

    public function testQuoted()
    {
        $this->performTest(
            '"foo"',
            $this->createToken(Lexer::TOKEN_STRING, 'foo', 1)
        );
    }

    public function testWhitespaceQuoted()
    {
        $this->performTest(
            '" "',
            $this->createToken(Lexer::TOKEN_STRING, ' ', 1)
        );
    }

    public function testSomeWhitespaceQuoted()
    {
        $this->performTest(
            '"foo and bar"',
            $this->createToken(Lexer::TOKEN_STRING, 'foo and bar', 1)
        );
    }

    public function testParenQuoted()
    {
        $this->performTest(
            '"(foo and bar)"',
            $this->createToken(Lexer::TOKEN_STRING, '(foo and bar)', 1)
        );
    }

    public function testAlternateQuotes()
    {
        $this->performTest(
            '"foo \'and\' bar"',
            $this->createToken(Lexer::TOKEN_STRING, 'foo \'and\' bar', 1)
        );
    }

    public function testEscapeQuote()
    {
        $this->performTest(
            '"foo \"and\" bar"',
            $this->createToken(Lexer::TOKEN_STRING, 'foo "and" bar', 1)
        );
    }

    public function testEscapeBackslash()
    {
        $this->performTest(
            '"foo \\\\"',
            $this->createToken(Lexer::TOKEN_STRING, 'foo \\', 1)
        );
    }

    public function testFunctionNoArgs()
    {
        $this->performTest(
            'foo()',
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 5)
        );
    }

    public function testFunctionStringArg()
    {
        $this->performTest(
            'foo("bar")',
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
            $this->createToken(Lexer::TOKEN_STRING, 'bar', 5),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 10)
        );
    }

    public function testFunctionStringArgWhitespace()
    {
        $this->performTest(
            'foo(    "bar"    )',
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
            $this->createToken(Lexer::TOKEN_WHITESPACE, '    ', 5),
            $this->createToken(Lexer::TOKEN_STRING, 'bar', 9),
            $this->createToken(Lexer::TOKEN_WHITESPACE, '    ', 14),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 18)
        );
    }

    public function testUnquotedWhitespace()
    {
        $this->performTest(
            'foo and    bar',
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 4),
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'and', 5),
            $this->createToken(Lexer::TOKEN_WHITESPACE, '    ', 8),
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'bar', 12)
        );
    }

    public function testFunctionStringArgs()
    {
        $this->performTest(
            'foo("bar", "baz")',
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
            $this->createToken(Lexer::TOKEN_STRING, 'bar', 5),
            $this->createToken(Lexer::TOKEN_COMMA, ',', 10),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 11),
            $this->createToken(Lexer::TOKEN_STRING, 'baz', 12),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 17)
        );
    }

    public function testInteger()
    {
        $this->performTest(
            '1337',
            $this->createToken(Lexer::TOKEN_UNQUOTED, '1337', 1)
        );
    }

    public function testDecimal()
    {
        $this->performTest(
            '1.3',
            $this->createToken(Lexer::TOKEN_UNQUOTED, '1.3', 1)
        );
    }

    public function testNestedFunctions()
    {
        $this->performTest(
            'foo("bar", bar(1, 3.14))',
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
            $this->createToken(Lexer::TOKEN_STRING, 'bar', 5),
            $this->createToken(Lexer::TOKEN_COMMA, ',', 10),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 11),
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'bar', 12),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 15),
            $this->createToken(Lexer::TOKEN_UNQUOTED, '1', 16),
            $this->createToken(Lexer::TOKEN_COMMA, ',', 17),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 18),
            $this->createToken(Lexer::TOKEN_UNQUOTED, '3.14', 19),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 23),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 24)
        );
    }

    public function testNonsenseBackslash()
    {
        $this->performTest(
            '\\\\\\',
            $this->createToken(Lexer::TOKEN_UNQUOTED, '\\', 1),
            $this->createToken(Lexer::TOKEN_UNQUOTED, '\\', 2),
            $this->createToken(Lexer::TOKEN_UNQUOTED, '\\', 3)
        );
    }

    public function testNonsenseParens()
    {
        $this->performTest(
            ')(',
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 2)
        );
    }

    public function testLexerResets()
    {
        $this->testNestedFunctions();
        $this->testNestedFunctions();
    }

    public function testUnterminatedString()
    {
        $this->performSyntaxErrorTest(
            '"foo',
            new UnexpectedEndOfInputException('"foo')
        );
    }

    private function performSyntaxErrorTest($input, $expected)
    {
        try {
            $this->getLexer()->tokenize($input);
        } catch (SyntaxErrorException $e) {
            $this->assertEquals($expected, $e);
            return;
        }
        
        $this->fail('Expected lexer to throw syntax error, but it did not');
    }
    
    private function performTest($input, ...$tokens)
    {
        $this->assertEquals($tokens, $this->getLexer()->tokenize($input));
    }

    private function getLexer()
    {
        return new DoctrineLexer();
    }
}