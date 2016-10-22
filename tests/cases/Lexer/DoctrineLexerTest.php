<?php

namespace Ehimen\JaslangTests\Lexer;

use Ehimen\Jaslang\Lexer\DoctrineLexer;
use Ehimen\Jaslang\Lexer\Lexer;
use PHPUnit\Framework\TestCase;

class DoctrineLexerTest extends TestCase
{

    public function testUnquoted()
    {
        $this->performTest(
            'foo',
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => 'foo',
                'position' => 1,
            ]
        );
    }

    public function testQuoted()
    {
        $this->performTest(
            '"foo"',
            [
                'type' => Lexer::TOKEN_QUOTED,
                'value' => 'foo',
                'position' => 1,
            ]
        );
    }

    public function testWhitespaceQuoted()
    {
        $this->performTest(
            '" "',
            [
                'type' => Lexer::TOKEN_QUOTED,
                'value' => ' ',
                'position' => 1,
            ]
        );
    }

    public function testSomeWhitespaceQuoted()
    {
        $this->performTest(
            '"foo and bar"',
            [
                'type' => Lexer::TOKEN_QUOTED,
                'value' => 'foo and bar',
                'position' => 1,
            ]
        );
    }

    public function testParenQuoted()
    {
        $this->performTest(
            '"(foo and bar)"',
            [
                'type' => Lexer::TOKEN_QUOTED,
                'value' => '(foo and bar)',
                'position' => 1,
            ]
        );
    }

    public function testAlternateQuotes()
    {
        $this->performTest(
            '"foo \'and\' bar"',
            [
                'type' => Lexer::TOKEN_QUOTED,
                'value' => 'foo \'and\' bar',
                'position' => 1,
            ]
        );
    }

    public function testEscapeQuote()
    {
        $this->performTest(
            '"foo \"and\" bar"',
            [
                'type' => Lexer::TOKEN_QUOTED,
                'value' => 'foo "and" bar',
                'position' => 1,
            ]
        );
    }

    public function testEscapeBackslash()
    {
        $this->performTest(
            '"foo \\\\"',
            [
                'type' => Lexer::TOKEN_QUOTED,
                'value' => 'foo \\',
                'position' => 1,
            ]
        );
    }

    public function testFunctionNoArgs()
    {
        $this->performTest(
            'foo()',
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => 'foo',
                'position' => 1,
            ],
            [
                'type' => Lexer::TOKEN_LEFT_PAREN,
                'value' => '(',
                'position' => 4,
            ],
            [
                'type' => Lexer::TOKEN_RIGHT_PAREN,
                'value' => ')',
                'position' => 5,
            ]
        );
    }

    public function testFunctionStringArg()
    {
        $this->performTest(
            'foo("bar")',
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => 'foo',
                'position' => 1,
            ],
            [
                'type' => Lexer::TOKEN_LEFT_PAREN,
                'value' => '(',
                'position' => 4,
            ],
            [
                'type' => Lexer::TOKEN_QUOTED,
                'value' => 'bar',
                'position' => 5,
            ],
            [
                'type' => Lexer::TOKEN_RIGHT_PAREN,
                'value' => ')',
                'position' => 10,
            ]
        );
    }

    public function testFunctionStringArgWhitespace()
    {
        $this->performTest(
            'foo(    "bar"    )',
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => 'foo',
                'position' => 1,
            ],
            [
                'type' => Lexer::TOKEN_LEFT_PAREN,
                'value' => '(',
                'position' => 4,
            ],
            [
                'type' => Lexer::TOKEN_WHITESPACE,
                'value' => '    ',
                'position' => 5,
            ],
            [
                'type' => Lexer::TOKEN_QUOTED,
                'value' => 'bar',
                'position' => 9,
            ],
            [
                'type' => Lexer::TOKEN_WHITESPACE,
                'value' => '    ',
                'position' => 14,
            ],
            [
                'type' => Lexer::TOKEN_RIGHT_PAREN,
                'value' => ')',
                'position' => 18,
            ]
        );
    }

    public function testUnquotedWhitespace()
    {
        $this->performTest(
            'foo and    bar',
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => 'foo',
                'position' => 1,
            ],
            [
                'type' => Lexer::TOKEN_WHITESPACE,
                'value' => ' ',
                'position' => 4,
            ],
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => 'and',
                'position' => 5,
            ],
            [
                'type' => Lexer::TOKEN_WHITESPACE,
                'value' => '    ',
                'position' => 8,
            ],
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => 'bar',
                'position' => 12,
            ]
        );
    }

    public function testFunctionStringArgs()
    {
        $this->performTest(
            'foo("bar", "baz")',
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => 'foo',
                'position' => 1,
            ],
            [
                'type' => Lexer::TOKEN_LEFT_PAREN,
                'value' => '(',
                'position' => 4,
            ],
            [
                'type' => Lexer::TOKEN_QUOTED,
                'value' => 'bar',
                'position' => 5,
            ],
            [
                'type' => Lexer::TOKEN_COMMA,
                'value' => ',',
                'position' => 10,
            ],
            [
                'type' => Lexer::TOKEN_WHITESPACE,
                'value' => ' ',
                'position' => 11,
            ],
            [
                'type' => Lexer::TOKEN_QUOTED,
                'value' => 'baz',
                'position' => 12,
            ],
            [
                'type' => Lexer::TOKEN_RIGHT_PAREN,
                'value' => ')',
                'position' => 17,
            ]
        );
    }

    public function testInteger()
    {
        $this->performTest(
            '1337',
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => '1337',
                'position' => 1,
            ]
        );
    }

    public function testDecimal()
    {
        $this->performTest(
            '1.3',
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => '1.3',
                'position' => 1,
            ]
        );
    }

    public function testNestedFunctions()
    {
        $this->performTest(
            'foo("bar", bar(1, 3.14))',
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => 'foo',
                'position' => 1,
            ],
            [
                'type' => Lexer::TOKEN_LEFT_PAREN,
                'value' => '(',
                'position' => 4,
            ],
            [
                'type' => Lexer::TOKEN_QUOTED,
                'value' => 'bar',
                'position' => 5,
            ],
            [
                'type' => Lexer::TOKEN_COMMA,
                'value' => ',',
                'position' => 10,
            ],
            [
                'type' => Lexer::TOKEN_WHITESPACE,
                'value' => ' ',
                'position' => 11,
            ],
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => 'bar',
                'position' => 12,
            ],
            [
                'type' => Lexer::TOKEN_LEFT_PAREN,
                'value' => '(',
                'position' => 15,
            ],
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => '1',
                'position' => 16,
            ],
            [
                'type' => Lexer::TOKEN_COMMA,
                'value' => ',',
                'position' => 17,
            ],
            [
                'type' => Lexer::TOKEN_WHITESPACE,
                'value' => ' ',
                'position' => 18,
            ],
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => '3.14',
                'position' => 19,
            ],
            [
                'type' => Lexer::TOKEN_RIGHT_PAREN,
                'value' => ')',
                'position' => 23,
            ],
            [
                'type' => Lexer::TOKEN_RIGHT_PAREN,
                'value' => ')',
                'position' => 24,
            ]
        );
    }

    public function testNonsenseBackslash()
    {
        $this->performTest(
            '\\\\\\',
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => '\\',
                'position' => 1,
            ],
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => '\\',
                'position' => 2,
            ],
            [
                'type' => Lexer::TOKEN_UNQUOTED,
                'value' => '\\',
                'position' => 3,
            ]
        );
    }

    public function testNonsenseParens()
    {
        $this->performTest(
            ')(',
            [
                'type' => Lexer::TOKEN_RIGHT_PAREN,
                'value' => ')',
                'position' => 1,
            ],
            [
                'type' => Lexer::TOKEN_LEFT_PAREN,
                'value' => '(',
                'position' => 2,
            ]
        );
    }

    public function testLexerResets()
    {
        $this->testNestedFunctions();
        $this->testNestedFunctions();
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