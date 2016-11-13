<?php

namespace Ehimen\JaslangTests;

use Ehimen\Jaslang\Engine\Ast\Identifier;
use Ehimen\Jaslang\Engine\Ast\Literal;
use Ehimen\Jaslang\Engine\Ast\Node;
use Ehimen\Jaslang\Engine\Ast\Operator;
use Ehimen\Jaslang\Engine\Ast\Root;
use Ehimen\Jaslang\Engine\Ast\Statement;
use Ehimen\Jaslang\Engine\FuncDef\OperatorSignature;
use Ehimen\Jaslang\Engine\Lexer\Token;
use Ehimen\Jaslang\Engine\Type\TypeRepository;
use PHPUnit\Framework\TestCase;
use Ehimen\Jaslang\Core\Type;

/**
 * @mixin TestCase
 * 
 * TODO: this is used by engine tests, but uses code from core.
 */
trait JaslangTestUtil
{
    private function createToken($type, $value, $position)
    {
        return new Token($value, $type, $position);
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

    private function identifier($value)
    {
        return new Identifier($value);
    }

    private function statement($children)
    {
        if (!is_array($children)) {
            $children = [$children];
        }
        
        return new Statement($children);
    }

    private function root(array $children)
    {
        return new Root($children);
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
}
