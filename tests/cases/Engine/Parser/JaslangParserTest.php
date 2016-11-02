<?php

namespace Ehimen\JaslangTests\Engine\Parser;

use Ehimen\Jaslang\Engine\Ast\Operator;
use Ehimen\Jaslang\Engine\Ast\Container;
use Ehimen\Jaslang\Engine\Ast\FunctionCall;
use Ehimen\Jaslang\Engine\Ast\Literal;
use Ehimen\Jaslang\Engine\Ast\Node;
use Ehimen\Jaslang\Engine\Ast\Root;
use Ehimen\Jaslang\Engine\FuncDef\FunctionRepository;
use Ehimen\Jaslang\Engine\FuncDef\OperatorSignature;
use Ehimen\Jaslang\Engine\Type\TypeRepository;
use Ehimen\Jaslang\Engine\Lexer\Lexer;
use Ehimen\Jaslang\Engine\Parser\Exception\SyntaxErrorException;
use Ehimen\Jaslang\Engine\Parser\Exception\UnexpectedEndOfInputException;
use Ehimen\Jaslang\Engine\Parser\Exception\UnexpectedTokenException;
use Ehimen\Jaslang\Engine\Parser\JaslangParser;
use Ehimen\JaslangTests\JaslangTestUtil;
use PHPUnit\Framework\TestCase;
use Ehimen\Jaslang\Core\Type;       // TODO: remove dependency on core.

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
                $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'bar', 5),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 10),
            ],
            new FunctionCall(
                'foo',
                [$this->stringLiteral('bar')]
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
                $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'bar', 5),
                $this->createToken(Lexer::TOKEN_COMMA, ',', 10),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 11),
                $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'baz', 12),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 17),
            ],
            new FunctionCall(
                'foo',
                [$this->stringLiteral('bar'), $this->stringLiteral('baz')]
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
            [$this->createToken(Lexer::TOKEN_LITERAL_STRING, 'foo', 1)],
            $this->stringLiteral('foo')
        );
    }

    public function testNumberLiteral()
    {
        $this->performTest(
            '3.14',
            [$this->createToken(Lexer::TOKEN_LITERAL, '3.14', 1)],
            $this->numberLiteral(3.14)
        );
    }

    public function testLeadingTrailingWhitespace()
    {
        $this->performTest(
            '  1337   ',
            [
                $this->createToken(Lexer::TOKEN_WHITESPACE, '  ', 1),
                $this->createToken(Lexer::TOKEN_LITERAL, '1337', 3),
                $this->createToken(Lexer::TOKEN_WHITESPACE, '   ', 6),
            ],
            $this->numberLiteral(1337)
        );
    }

    public function testRepeatedString()
    {
        $this->performSyntaxErrorTest(
            '"foo" "bar"',
            [
                $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'foo', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 5),
                $unexpected = $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'bar', 6),
            ],
            $this->unexpectedTokenException('"foo" "bar"', $unexpected)
        );
    }

    public function testRepeatedLiterals()
    {
        $this->markTestSkipped('TODO: need to make this syntax error again, but how?');
        
        $this->performSyntaxErrorTest(
            '"foo" 1337',
            [
                $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'foo', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 5),
                $unexpected = $this->createToken(Lexer::TOKEN_LITERAL, '1337', 6),
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
            $this->unexpectedEndOfInputException('(')
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

    public function testBinaryOperator()
    {
        $this->performTest(
            '3 + 4',
            [
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 2),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 3),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 4),
                $this->createToken(Lexer::TOKEN_LITERAL, '4', 5)
            ],
            $this->binaryOperator('+', [$this->numberLiteral(3), $this->numberLiteral(4)])
        );
    }

    public function testChainedBinaryOperator()
    {
        $this->performTest(
            '3 + 4 + 5',
            [
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 2),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 3),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 4),
                $this->createToken(Lexer::TOKEN_LITERAL, '4', 5),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 6),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 7),
                $this->createToken(Lexer::TOKEN_LITERAL, '5', 8)
            ],
            $this->binaryOperator(
                '+',
                [
                    $this->binaryOperator('+', [$this->numberLiteral(3), $this->numberLiteral(4)]),
                    $this->numberLiteral(5),
                ]
            )
        );
    }

    public function testComplexChainedBinaryOperator()
    {
        $this->performTest(
            'sum(3+4-5,6+7-8+9)',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'sum', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 5),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 6),
                $this->createToken(Lexer::TOKEN_LITERAL, '4', 7),
                $this->createToken(Lexer::TOKEN_OPERATOR, '-', 8),
                $this->createToken(Lexer::TOKEN_LITERAL, '5', 9),
                $this->createToken(Lexer::TOKEN_COMMA, ',', 10),
                $this->createToken(Lexer::TOKEN_LITERAL, '6', 11),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 12),
                $this->createToken(Lexer::TOKEN_LITERAL, '7', 13),
                $this->createToken(Lexer::TOKEN_OPERATOR, '-', 14),
                $this->createToken(Lexer::TOKEN_LITERAL, '8', 15),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 16),
                $this->createToken(Lexer::TOKEN_LITERAL, '9', 17),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 18),
            ],
            new FunctionCall(
                'sum',
                [
                    $this->binaryOperator(
                        '-',
                        [
                            $this->binaryOperator('+', [$this->numberLiteral(3), $this->numberLiteral(4)]),
                            $this->numberLiteral(5),
                        ]
                    ),
                    $this->binaryOperator(
                        '+',
                        [
                            $this->binaryOperator(
                                '-',
                                [
                                    $this->binaryOperator('+', [$this->numberLiteral(6), $this->numberLiteral(7)]),
                                    $this->numberLiteral(8),
                                ]
                            ),
                            $this->numberLiteral(9),
                        ]
                    )
                ]
            )
        );
    }

    public function testBooleanTrue()
    {
        $this->performTest(
            'true',
            [$this->createToken(Lexer::TOKEN_LITERAL, 'true', 1)],
            $this->booleanLiteral('true')
        );
    }

    public function testBooleanFalse()
    {
        $this->performTest(
            'false',
            [$this->createToken(Lexer::TOKEN_LITERAL, 'false', 1)],
            $this->booleanLiteral('false')
        );
    }

    public function testFunctionOperator()
    {
        $this->performTest(
            'sum(1, 1) + 2',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'sum', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 5),
                $this->createToken(Lexer::TOKEN_COMMA, ',', 6),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 7),
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 8),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 9),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 10),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 11),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 12),
                $this->createToken(Lexer::TOKEN_LITERAL, '2', 13),
            ],
            $this->binaryOperator(
                '+',
                [
                    new FunctionCall(
                        'sum',
                        [
                            $this->numberLiteral('1'),
                            $this->numberLiteral('1'),
                        ]
                    ),
                    $this->numberLiteral('2'),
                ]
            )
        );
    }

    public function testFunctionOperatorFunction()
    {
        $this->performTest(
            'sum(1,2+sum(3,4)+5)',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'sum', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 5),
                $this->createToken(Lexer::TOKEN_COMMA, ',', 6),
                $this->createToken(Lexer::TOKEN_LITERAL, '2', 7),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 8),
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'sum', 9),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 12),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 13),
                $this->createToken(Lexer::TOKEN_COMMA, ',', 14),
                $this->createToken(Lexer::TOKEN_LITERAL, '4', 15),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 16),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 17),
                $this->createToken(Lexer::TOKEN_LITERAL, '5', 18),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 19),
            ],
            new FunctionCall(
                'sum',
                [
                    $this->numberLiteral('1'),
                    $this->binaryOperator(
                        '+',
                        [
                            $this->binaryOperator(
                                '+',
                                [
                                    $this->numberLiteral('2'),
                                    new FunctionCall(
                                        'sum',
                                        [
                                            $this->numberLiteral('3'),
                                            $this->numberLiteral('4'),
                                        ]
                                    ),
                                ]
                            ),
                            $this->numberLiteral('5'),
                        ]
                    ),
                ]
            )
        );
    }

    public function testParenGrouping()
    {
        $this->performTest(
            '1+(2+3)',
            [
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 1),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 2),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 3),
                $this->createToken(Lexer::TOKEN_LITERAL, '2', 4),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 5),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 6),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 7),
            ],
            $this->binaryOperator(
                '+',
                [
                    $this->numberLiteral('1'),
                    new Container(
                        $this->binaryOperator('+', [$this->numberLiteral('2'), $this->numberLiteral('3')])
                    ),
                ]
            )
        );
    }

    public function testConsecutiveParen()
    {
        $this->performTest(
            '(((3)))',
            [
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 2),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 3),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 4),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 5),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 6),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 7),
            ],
            new Container(
                new Container(
                    new Container(
                        $this->numberLiteral('3')
                    )
                )
            )
        );
    }

    public function testMultiStatement()
    {
        $this->performMultiStatementTest(
            '1;2;3;4',
            [
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 1),
                $this->createToken(Lexer::TOKEN_STATETERM, ';', 2),
                $this->createToken(Lexer::TOKEN_LITERAL, '2', 3),
                $this->createToken(Lexer::TOKEN_STATETERM, ';', 4),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 5),
                $this->createToken(Lexer::TOKEN_STATETERM, ';', 6),
                $this->createToken(Lexer::TOKEN_LITERAL, '4', 7),
            ],
            new Root([
                $this->numberLiteral('1'),
                $this->numberLiteral('2'),
                $this->numberLiteral('3'),
                $this->numberLiteral('4'),
            ])
        );
    }

    public function testMultiStatementOperator()
    {
        $this->performMultiStatementTest(
            '1+1;2+2+2;3+3',
            [
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 1),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 2),
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 3),
                $this->createToken(Lexer::TOKEN_STATETERM, ';', 4),
                $this->createToken(Lexer::TOKEN_LITERAL, '2', 5),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 6),
                $this->createToken(Lexer::TOKEN_LITERAL, '2', 7),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 8),
                $this->createToken(Lexer::TOKEN_LITERAL, '2', 9),
                $this->createToken(Lexer::TOKEN_STATETERM, ';', 10),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 11),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 12),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 13),
            ],
            new Root([
                $this->binaryOperator(
                    '+',
                    [
                        $this->numberLiteral('1'),
                        $this->numberLiteral('1'),
                    ]
                ),
                $this->binaryOperator(
                    '+',
                    [
                        $this->binaryOperator(
                            '+',
                            [
                                $this->numberLiteral('2'),
                                $this->numberLiteral('2'),
                            ]
                        ),
                        $this->numberLiteral('2'),
                    ]
                ),
                $this->binaryOperator(
                    '+',
                    [
                        $this->numberLiteral('3'),
                        $this->numberLiteral('3'),
                    ]
                ),
            ])
        );
    }

    public function testMultiStatementFunction()
    {
        $this->performMultiStatementTest(
            '1;sum(2,3+4);sum(5,6)',
            [
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 1),
                $this->createToken(Lexer::TOKEN_STATETERM, ';', 2),
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'sum', 3),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 6),
                $this->createToken(Lexer::TOKEN_LITERAL, '2', 7),
                $this->createToken(Lexer::TOKEN_COMMA, ',', 8),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 9),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 10),
                $this->createToken(Lexer::TOKEN_LITERAL, '4', 11),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 12),
                $this->createToken(Lexer::TOKEN_STATETERM, ';', 13),
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'sum', 14),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 17),
                $this->createToken(Lexer::TOKEN_LITERAL, '5', 18),
                $this->createToken(Lexer::TOKEN_COMMA, ',', 19),
                $this->createToken(Lexer::TOKEN_LITERAL, '6', 20),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, '6', 21),
            ],
            new Root([
                $this->numberLiteral('1'),
                new FunctionCall(
                    'sum',
                    [
                        $this->numberLiteral('2'),
                        $this->binaryOperator(
                            '+',
                            [
                                $this->numberLiteral('3'),
                                $this->numberLiteral('4'),
                            ]
                        ),
                    ]
                ),
                new FunctionCall(
                    'sum',
                    [
                        $this->numberLiteral('5'),
                        $this->numberLiteral('6'),
                    ]
                ),
            ])
        );
    }

    public function testPrefixUnaryOperator()
    {
        $signature = OperatorSignature::prefixUnary();

        $this->performTestWithOperators(
            '++3',
            [
                $this->createToken(Lexer::TOKEN_OPERATOR, '++', 1),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 3),
            ],
            $this->operator('++', [$this->numberLiteral('3')], $signature),
            [
                ['++', $signature],
            ]
        );
    }

    public function testPrefixBinaryOperator()
    {
        $signature = new OperatorSignature(0, 2);

        $this->performTestWithOperators(
            '++ 3 4',
            [
                $this->createToken(Lexer::TOKEN_OPERATOR, '++', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 3),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 4),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 5),
                $this->createToken(Lexer::TOKEN_LITERAL, '4', 6),
            ],
            $this->operator(
                '++',
                [$this->numberLiteral('3'), $this->numberLiteral('4')],
                $signature
            ),
            [
                ['++', $signature],
            ]
        );
    }

    public function testPostfixUnaryOperator()
    {
        $signature = OperatorSignature::postfixUnary();

        $this->performTestWithOperators(
            '3++',
            [
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 1),
                $this->createToken(Lexer::TOKEN_OPERATOR, '++', 2),
            ],
            $this->operator('++', [$this->numberLiteral('3')], $signature),
            [
                ['++', $signature],
            ]
        );
    }

    public function testPostfixBinaryOperator()
    {
        $signature = new OperatorSignature(2, 0);

        $this->performTestWithOperators(
            '4 3 ++',
            [
                $this->createToken(Lexer::TOKEN_LITERAL, '4', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 2),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 3),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 4),
                $this->createToken(Lexer::TOKEN_OPERATOR, '++', 5),
            ],
            $this->operator(
                '++',
                [$this->numberLiteral('4'), $this->numberLiteral('3')],
                $signature
            ),
            [
                ['++', $signature],
            ]
        );
    }

    public function testNaryOperator()
    {
        $signature = new OperatorSignature(3, 2);

        $this->performTestWithOperators(
            '4 3 2 ++ 5 6',
            [
                $this->createToken(Lexer::TOKEN_LITERAL, '4', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 2),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 3),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 4),
                $this->createToken(Lexer::TOKEN_LITERAL, '2', 5),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 6),
                $this->createToken(Lexer::TOKEN_OPERATOR, '++', 7),
                $this->createToken(Lexer::TOKEN_LITERAL, '5', 9),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 10),
                $this->createToken(Lexer::TOKEN_LITERAL, '6', 11),
            ],
            $this->operator(
                '++',
                [
                    $this->numberLiteral('4'),
                    $this->numberLiteral('3'),
                    $this->numberLiteral('2'),
                    $this->numberLiteral('5'),
                    $this->numberLiteral('6'),
                ],
                $signature
            ),
            [
                ['++', $signature],
            ]
        );
    }

    public function testNaryOperatorPrecedence()
    {
        $prefix  = new OperatorSignature(0, 2, 0);
        $postfix = new OperatorSignature(3, 0, 10);
        
        $this->performTestWithOperators(
            '++ 3 2 1 -- 4',        // Should be interpreted: ++ (3 2 1 --) 4
            [
                $this->createToken(Lexer::TOKEN_OPERATOR, '++', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 3),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 4),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 5),
                $this->createToken(Lexer::TOKEN_LITERAL, '2', 6),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 7),
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 8),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 9),
                $this->createToken(Lexer::TOKEN_OPERATOR, '--', 11),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 12),
                $this->createToken(Lexer::TOKEN_LITERAL, '4', 13),
            ],
            $this->operator(
                '++',
                [
                    $this->operator(
                        '--',
                        [
                            $this->numberLiteral('3'),
                            $this->numberLiteral('2'),
                            $this->numberLiteral('1'),
                        ],
                        $postfix
                    ),
                    $this->numberLiteral('4'),
                ],
                $prefix
            ),
            [
                ['++', $prefix],
                ['--', $postfix],
            ]
        );
    }

    public function testNaryOperatorDefaultPrecedence()
    {
        $prefix  = new OperatorSignature(0, 2);
        $postfix = new OperatorSignature(3, 0);
        
        $this->performTestWithOperators(
            '4 ++ 3 2 1 --',        // Should be interpreted: 4 (++ 3 2) 1 --
            [
                $this->createToken(Lexer::TOKEN_LITERAL, '4', 1),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 2),
                $this->createToken(Lexer::TOKEN_OPERATOR, '++', 3),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 5),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 6),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 7),
                $this->createToken(Lexer::TOKEN_LITERAL, '2', 8),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 9),
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 10),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 11),
                $this->createToken(Lexer::TOKEN_OPERATOR, '--', 12),
            ],
            $this->operator(
                '--',
                [
                    $this->numberLiteral('4'),
                    $this->operator(
                        '++',
                        [
                            $this->numberLiteral('3'),
                            $this->numberLiteral('2'),
                        ],
                        $prefix
                    ),
                    $this->numberLiteral('1'),
                ],
                $postfix
            ),
            [
                ['++', $prefix],
                ['--', $postfix],
            ]
        );
    }

    public function testOperatorPrecedence()
    {
        $this->performTestWithOperators(
            '1+3-1',
            [
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 1),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 2),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 3),
                $this->createToken(Lexer::TOKEN_OPERATOR, '-', 4),
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 5),
            ],
            $this->binaryOperator(
                '+',
                [
                    $this->numberLiteral('1'),
                    $this->binaryOperator(
                        '-',
                        [
                            $this->numberLiteral('3'),
                            $this->numberLiteral('1'),
                        ],
                        10
                    ),
                ],
                0
            ),
            [
                ['+', OperatorSignature::binary(0)],
                ['-', OperatorSignature::binary(10)],      // Subtract is higher precedence than sum.
            ]
        );
    }

    public function testOperatorPrecedenceFunction()
    {
        $this->performTestWithOperators(
            '1-3+1-sum(5+9-1+4,1+2)',
            [
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 1),
                $this->createToken(Lexer::TOKEN_OPERATOR, '-', 2),
                $this->createToken(Lexer::TOKEN_LITERAL, '3', 3),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 4),
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 5),
                $this->createToken(Lexer::TOKEN_OPERATOR, '-', 6),
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'sum', 7),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 10),
                $this->createToken(Lexer::TOKEN_LITERAL, '5', 11),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 12),
                $this->createToken(Lexer::TOKEN_LITERAL, '9', 13),
                $this->createToken(Lexer::TOKEN_OPERATOR, '-', 14),
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 15),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 16),
                $this->createToken(Lexer::TOKEN_LITERAL, '4', 17),
                $this->createToken(Lexer::TOKEN_COMMA, ',', 18),
                $this->createToken(Lexer::TOKEN_LITERAL, '1', 19),
                $this->createToken(Lexer::TOKEN_OPERATOR, '+', 20),
                $this->createToken(Lexer::TOKEN_LITERAL, '2', 21),
                $this->createToken(Lexer::TOKEN_RIGHT_PAREN, ')', 22),
            ],
            $this->binaryOperator(
                '-',
                [
                    $this->binaryOperator(
                        '-',
                        [
                            $this->numberLiteral('1'),
                            $this->binaryOperator(
                                '+',
                                [
                                    $this->numberLiteral('3'),
                                    $this->numberLiteral('1'),
                                ],
                                10
                            ),
                        ],
                        0
                    ),
                    new FunctionCall(
                        'sum',
                        [
                            $this->binaryOperator(
                                '-',
                                [
                                    $this->binaryOperator(
                                        '+',
                                        [
                                            $this->numberLiteral('5'),
                                            $this->numberLiteral('9'),
                                        ],
                                        10
                                    ),
                                    $this->binaryOperator(
                                        '+',
                                        [
                                            $this->numberLiteral('1'),
                                            $this->numberLiteral('4'),
                                        ],
                                        10
                                    ),
                                ],
                                0
                            ),
                            $this->binaryOperator(
                                '+',
                                [
                                    $this->numberLiteral('1'),
                                    $this->numberLiteral('2'),
                                ],
                                10
                            )
                        ]
                    ),
                ],
                0
            ),
            [
                ['-', OperatorSignature::binary(0)],
                ['+', OperatorSignature::binary(10)],      // Subtract is higher precedence than sum.
            ]
        );
    }

    public function testMissingComma()
    {
        $this->performSyntaxErrorTest(
            'foo("foo" "bar")',
            [
                $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1),
                $this->createToken(Lexer::TOKEN_LEFT_PAREN, '(', 4),
                $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'foo', 5),
                $this->createToken(Lexer::TOKEN_WHITESPACE, ' ', 10),
                $unexpected = $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'bar', 11),
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
    
    // TODO: possible uncaught syntax errors with commas, e.g. foo("bar"),,,

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
        $actual = $this
            ->getParser($this->getLexer($input, $tokens))
            ->parse($input)
            ->getFirstChild();

        $this->assertEquals($expected, $actual);
    }

    private function performMultiStatementTest($input, $tokens, Node $expected)
    {
        $actual = $this->getParser($this->getLexer($input, $tokens))->parse($input);

        $this->assertEquals($expected, $actual);
    }

    private function performTestWithOperators($input, $tokens, Node $expected, $operators)
    {
        $parser = $this->getParser($this->getLexer($input, $tokens), $this->getFunctionRepository($operators));
        $actual = $parser->parse($input)->getFirstChild();

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

    private function getFunctionRepository(array $operatorSignatures = [])
    {
        $repo = $this->createMock(FunctionRepository::class);

        $signatureMethod = $repo->method('getOperatorSignature');

        if (!empty($operatorSignatures)) {
            $signatureMethod->willReturnMap($operatorSignatures);
        } else {
            $signatureMethod->willReturn(OperatorSignature::binary());
        }

        return $repo;
    }

    private function getTypeRepository(array $concreteTypes = [])
    {
        if (empty($concreteTypes)) {
            $concreteTypes = [
                new Type\Str(),
                new Type\Num(),
                new Type\Boolean(),
            ];
        }
        
        $repo = $this->createMock(TypeRepository::class);
        
        $repo->method('getConcreteTypes')
            ->willReturn($concreteTypes);
        
        return $repo;
    }

    private function unexpectedTokenException($input, $token)
    {
        return new UnexpectedTokenException($input, $token);
    }

    private function unexpectedEndOfInputException($input)
    {
        return new UnexpectedEndOfInputException($input);
    }

    private function getParser(Lexer $lexer, FunctionRepository $fnRepo = null, TypeRepository $typeRepo = null)
    {
        $fnRepo   = $fnRepo ?: $this->getFunctionRepository();
        $typeRepo = $typeRepo ?: $this->getTypeRepository();

        return new JaslangParser($lexer, $fnRepo, $typeRepo);
    }

    private function stringLiteral($value)
    {
        return new Literal(new Type\Str(), $value);
    }

    private function numberLiteral($value)
    {
        return new Literal(new Type\Num(), $value);
    }

    private function booleanLiteral($value)
    {
        return new Literal(new Type\Boolean(), $value);
    }

    /**
     * @return Operator
     */
    private function binaryOperator(
        $operator,
        array $children,
        $precedence = OperatorSignature::OPERATOR_PRECEDENCE_DEFAULT
    ) {
        return $this->operator($operator, $children, OperatorSignature::binary($precedence));
    }

    private function operator($operator, array $children, OperatorSignature $signature)
    {
        $operator = new Operator($operator, $signature);

        foreach ($children as $child) {
            $operator->addChild($child);
        }

        return $operator;
    }
}
