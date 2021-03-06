<?php

namespace Ehimen\JaslangTests\Engine\Lexer;

use Ehimen\Jaslang\Engine\Lexer\JaslangLexer;
use Ehimen\Jaslang\Engine\Lexer\Lexer;
use Ehimen\Jaslang\Engine\Parser\Exception\SyntaxErrorException;
use Ehimen\Jaslang\Engine\Parser\Exception\UnexpectedEndOfInputException;
use Ehimen\Jaslang\Core\Type\Boolean;       // TODO: remove dependency on non-engine!
use Ehimen\Jaslang\Core\Type\Num;
use Ehimen\JaslangTests\JaslangTestUtil;
use PHPUnit\Framework\TestCase;

class JaslangLexerTest extends TestCase
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
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'foo', 1)
        );
    }

    public function testWhitespaceQuoted()
    {
        $this->performTest(
            '" "',
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, ' ', 1)
        );
    }

    public function testSomeWhitespaceQuoted()
    {
        $this->performTest(
            '"foo and bar"',
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'foo and bar', 1)
        );
    }

    public function testParenQuoted()
    {
        $this->performTest(
            '"(foo and bar)"',
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, '(foo and bar)', 1)
        );
    }

    public function testAlternateQuotes()
    {
        $this->performTest(
            '"foo \'and\' bar"',
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'foo \'and\' bar', 1)
        );
    }

    public function testEscapeQuote()
    {
        $this->performTest(
            '"foo \"and\" bar"',
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'foo "and" bar', 1)
        );
    }

    public function testEscapeBackslash()
    {
        $this->performTest(
            '"foo \\\\"',
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'foo \\', 1)
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
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'bar', 5),
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
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'bar', 9),
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
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'bar', 5),
            $this->createToken(Lexer::TOKEN_COMMA, ',', 10),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 11),
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'baz', 12),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 17)
        );
    }

    public function testInteger()
    {
        $this->performTestWithLiteralPatterns(
            '1337',
            [Num::LITERAL_PATTERN],
            $this->createToken(Lexer::TOKEN_LITERAL, '1337', 1)
        );
    }

    public function testDecimal()
    {
        $this->performTestWithLiteralPatterns(
            '1.3',
            [Num::LITERAL_PATTERN],
            $this->createToken(Lexer::TOKEN_LITERAL, '1.3', 1)
        );
    }

    public function testNestedFunctions()
    {
        $this->performTestWithLiteralPatterns(
            'foo("bar", bar(1, 3.14))',
            [Num::LITERAL_PATTERN],
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'bar', 5),
            $this->createToken(Lexer::TOKEN_COMMA, ',', 10),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 11),
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'bar', 12),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 15),
            $this->createToken(Lexer::TOKEN_LITERAL, '1', 16),
            $this->createToken(Lexer::TOKEN_COMMA, ',', 17),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 18),
            $this->createToken(Lexer::TOKEN_LITERAL, '3.14', 19),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 23),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 24)
        );
    }

    public function testNonsenseBackslash()
    {
        $this->performTest(
            '\\\\\\',
            $this->createToken(Lexer::TOKEN_BACKSLASH, '\\', 1),
            $this->createToken(Lexer::TOKEN_BACKSLASH, '\\', 2),
            $this->createToken(Lexer::TOKEN_BACKSLASH, '\\', 3)
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

    public function testNotRecognisedSymbol()
    {
        $this->performTest(
            '@~#.#@',      // TODO: Doctrine lexer doesn't support multibyte :(
            $this->createToken(Lexer::TOKEN_UNKNOWN, '@', 1),
            $this->createToken(Lexer::TOKEN_UNKNOWN, '~', 2),
            $this->createToken(Lexer::TOKEN_UNKNOWN, '#', 3),
            $this->createToken(Lexer::TOKEN_UNKNOWN, '.', 4),
            $this->createToken(Lexer::TOKEN_UNKNOWN, '#', 5),
            $this->createToken(Lexer::TOKEN_UNKNOWN, '@', 6)
        );
    }

    public function testLexerResets()
    {
        $this->testNestedFunctions();
        $this->testNestedFunctions();
    }

    public function testAdditionOperator()
    {
        $this->performTestWithOperatorsAndLiteralPatterns(
            "3 + 4",
            ['+'],
            [Num::LITERAL_PATTERN],
            $this->createToken(Lexer::TOKEN_LITERAL, '3', 1),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 2),
            $this->createToken(Lexer::TOKEN_OPERATOR, '+', 3),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 4),
            $this->createToken(Lexer::TOKEN_LITERAL, '4', 5)
        );
    }

    public function testAdditionOperatorInFunction()
    {
        $this->performTestWithOperatorsAndLiteralPatterns(
            "foo(3 + 4)",
            ['+'],
            [Num::LITERAL_PATTERN],
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
            $this->createToken(Lexer::TOKEN_LITERAL, '3', 5),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 6),
            $this->createToken(Lexer::TOKEN_OPERATOR, '+', 7),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 8),
            $this->createToken(Lexer::TOKEN_LITERAL, '4', 9),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 10)
        );
    }

    public function testSignedNumbers()
    {
        $this->performTestWithOperatorsAndLiteralPatterns(
            '+3.14 - -26',
            ['-'],
            [Num::LITERAL_PATTERN],
            $this->createToken(Lexer::TOKEN_LITERAL, '+3.14', 1),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 6),
            $this->createToken(Lexer::TOKEN_OPERATOR, '-', 7),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 8),
            $this->createToken(Lexer::TOKEN_LITERAL, '-26', 9)
        );
    }

    public function testBoolean()
    {
        $this->performTestWithLiteralPatterns(
            'foo(true, false)',
            [Boolean::LITERAL_PATTERN],
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
            $this->createToken(Lexer::TOKEN_LITERAL, 'true', 5),
            $this->createToken(Lexer::TOKEN_COMMA, ',', 9),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 10),
            $this->createToken(Lexer::TOKEN_LITERAL, 'false', 11),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 16)
        );
    }

    public function testHangingSigned()
    {
        $this->performTestWithLiteralPatterns(
            '+3.',
            [Num::LITERAL_PATTERN],
            $this->createToken(Lexer::TOKEN_LITERAL, '+3.', 1)
        );
    }

    public function testArbitraryOperator()
    {
        $this->performTestWithOperatorsAndLiteralPatterns(
            "foo(3 +=/*!<>-^ 4)",
            ['+=/*!<>-^'],
            [Num::LITERAL_PATTERN],
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
            $this->createToken(Lexer::TOKEN_LITERAL, '3', 5),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 6),
            $this->createToken(Lexer::TOKEN_OPERATOR, '+=/*!<>-^', 7),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 16),
            $this->createToken(Lexer::TOKEN_LITERAL, '4', 17),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 18)
        );
    }

    public function testLoneCustomOperator()
    {
        $this->performTestWithOperators(
            'OR',
            ['OR'],
            $this->createToken(Lexer::TOKEN_OPERATOR, 'OR', 1)
        );
    }

    public function testCustomOperator()
    {
        $this->performTestWithOperatorsAndLiteralPatterns(
            'foo() AND 3.14',
            ['AND'],
            [Num::LITERAL_PATTERN],
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 5),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 6),
            $this->createToken(Lexer::TOKEN_OPERATOR, 'AND', 7),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 10),
            $this->createToken(Lexer::TOKEN_LITERAL, '3.14', 11)
        );
    }

    public function testCustomOperators()
    {
        $this->performTestWithOperatorsAndLiteralPatterns(
            'foo() OR (3.14 bar true)',
            ['OR', 'bar'],
            [Boolean::LITERAL_PATTERN, Num::LITERAL_PATTERN],
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 5),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 6),
            $this->createToken(Lexer::TOKEN_OPERATOR, 'OR', 7),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 9),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 10),
            $this->createToken(Lexer::TOKEN_LITERAL, '3.14', 11),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 15),
            $this->createToken(Lexer::TOKEN_OPERATOR, 'bar', 16),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 19),
            $this->createToken(Lexer::TOKEN_LITERAL, 'true', 20),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 24)
        );
    }

    public function testOperatorInString()
    {
        $this->performTestWithOperators(
            '"fooANDbar" AND "bar"',
            ['AND'],
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'fooANDbar', 1),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 12),
            $this->createToken(Lexer::TOKEN_OPERATOR, 'AND', 13),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 16),
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'bar', 17)
        );
    }

    public function testMultiStatements()
    {
        $this->performTest(
            'foo; bar; baz',
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
            $this->createToken(Lexer::TOKEN_STATETERM, ';', 4),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 5),
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'bar', 6),
            $this->createToken(Lexer::TOKEN_STATETERM, ';', 9),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 10),
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'baz', 11)
        );
    }

    public function testUnterminatedString()
    {
        $this->performSyntaxErrorTest(
            '"foo',
            new UnexpectedEndOfInputException('"foo')
        );
    }

    public function testLiteralNotIdentifier()
    {
        $this->performTestWithLiteralPatterns(
            'foo',
            ['foo'],
            $this->createToken(Lexer::TOKEN_LITERAL, 'foo', 1)
        );
    }

    public function testIdentifierNotLiteral()
    {
        $this->performTestWithLiteralPatterns(
            'foobar foo',
            ['^foo$'],
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foobar', 1),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 7),
            $this->createToken(Lexer::TOKEN_LITERAL, 'foo', 8)
        );
    }

    public function testNumericIdentifier()
    {
        $this->performTestWithLiteralPatterns(
            '3.14foo',
            [Num::LITERAL_PATTERN],
            $this->createToken(Lexer::TOKEN_LITERAL, '3.14', 1),
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 5)
        );
    }

    public function testMultibyte()
    {
        $this->performTestWithOperators(
            'ѥ£€Ҧ("Ӕ")',
            ['ѥ£€Ҧ'],
            $this->createToken(Lexer::TOKEN_OPERATOR, 'ѥ£€Ҧ', 1),
            $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 5),
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'Ӕ', 6),
            $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 9)
        );
    }

    public function testBraces()
    {
        $this->performTestWithOperators(
            '{foo = bar}',
            ['='],
            $this->createToken(Lexer::TOKEN_LEFT_BRACE, '{', 1),
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 2),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 5),
            $this->createToken(Lexer::TOKEN_OPERATOR, '=', 6),
            $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 7),
            $this->createToken(Lexer::TOKEN_IDENTIFIER, 'bar', 8),
            $this->createToken(Lexer::TOKEN_RIGHT_BRACE, '}', 11)
        );
    }

    public function testQuotedSpecialChars()
    {
        $this->performTest(
            '"{}(),"',
            $this->createToken(Lexer::TOKEN_LITERAL_STRING, '{}(),', 1)
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

    private function performTestWithOperators($input, $operators, ...$tokens)
    {
        $this->assertEquals($tokens, $this->getLexer($operators)->tokenize($input));
    }

    private function performTestWithOperatorsAndLiteralPatterns(
        $input,
        array $operators,
        array $literalPatterns,
        ...$tokens
    ) {
        $this->assertEquals($tokens, $this->getLexer($operators, $literalPatterns)->tokenize($input));
    }

    private function performTestWithLiteralPatterns($input, $literalPatterns, ...$tokens)
    {
        $this->assertEquals($tokens, $this->getLexer([], $literalPatterns)->tokenize($input));
    }

    private function getLexer(array $operators = [], array $literalPatterns = [])
    {
        return new JaslangLexer($operators, $literalPatterns);
    }
}
