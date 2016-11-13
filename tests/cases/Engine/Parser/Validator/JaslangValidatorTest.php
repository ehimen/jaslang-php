<?php

namespace Ehimen\Jaslang\Engine\Parser\Validator;

use Ehimen\Jaslang\Engine\Ast\Root;
use Ehimen\Jaslang\Engine\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\Lexer\Lexer;
use Ehimen\Jaslang\Engine\Lexer\Token;
use Ehimen\Jaslang\Engine\Parser\Exception\SyntaxErrorException;
use Ehimen\Jaslang\Engine\Parser\Exception\UnexpectedTokenException;
use Ehimen\JaslangTests\JaslangTestUtil;
use PHPUnit\Framework\TestCase;

class JaslangValidatorTest extends TestCase
{
    use JaslangTestUtil;

    public function throwsIfRootDoesNotContainStatements()
    {
        $validator = $this->getValidator();
        $ast       = $this->root([$this->numberLiteral('13')]);
        
        $this->expectException(RuntimeException::class);
        
        $validator->validate('13', $ast);
    }
    
    public function testThrowsIfRepeatedLiterals()
    {
        $input = '"foo" 1337';
        
        $stringToken = $this->createToken(Lexer::TOKEN_LITERAL_STRING, 'foo', 1);
        $numberToken = $this->createToken(Lexer::TOKEN_LITERAL, '1337', 7);
        
        $expected = new UnexpectedTokenException($input, $numberToken);
        
        $ast = $this->root([$this->statement([
            $stringNode = $this->stringLiteral('foo'),
            $numberNode = $this->numberLiteral('1337'),
        ])]);
        
        $validator = $this->getValidator();
        $validator->onNodeCreated($stringNode, $stringToken);
        $validator->onNodeCreated($numberNode, $numberToken);
        
        $this->performSyntaxErrorTest($validator, $input, $ast, $expected);
    }
    
    public function testThrowsIfRepeatedIdentifiers()
    {
        $input = 'foo bar';
        
        $ident1Token = $this->createToken(Lexer::TOKEN_IDENTIFIER, 'foo', 1);
        $ident2Token = $this->createToken(Lexer::TOKEN_IDENTIFIER, 'bar', 5);

        $expected = new UnexpectedTokenException($input, $ident2Token);
        
        $ast = $this->root([$this->statement([
            $ident1Node = $this->identifier('foo'),
            $ident2Node = $this->identifier('bar'),
        ])]);
        
        $validator = $this->getValidator();
        $validator->onNodeCreated($ident1Node, $ident1Token);
        $validator->onNodeCreated($ident2Node, $ident2Token);
        
        $this->performSyntaxErrorTest($validator, $input, $ast, $expected);
    }

    private function performSyntaxErrorTest(Validator $validator, $input , Root $ast, SyntaxErrorException $expected)
    {
        try {
            $validator->validate($input, $ast);
        } catch (SyntaxErrorException $actual) {
            $this->assertEquals($expected, $actual);
            
            return;
        }
        
        $this->fail('Did not throw syntax error');
    }

    /**
     * @return JaslangValidator
     */
    private function getValidator()
    {
        return new JaslangValidator();
    }
}
