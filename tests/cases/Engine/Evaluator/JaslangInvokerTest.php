<?php

namespace Ehimen\JaslangTests\Engine\Evaluator;

use Ehimen\Jaslang\Engine\Evaluator\Context\EvaluationContext;
use Ehimen\Jaslang\Engine\Evaluator\Evaluator;
use Ehimen\Jaslang\Engine\Evaluator\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Engine\Evaluator\JaslangInvoker;
use Ehimen\Jaslang\Engine\FuncDef\Arg\ArgList;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Expected\Parameter;
use Ehimen\Jaslang\Engine\FuncDef\Arg\TypeIdentifier;
use Ehimen\Jaslang\Engine\FuncDef\Arg\Variable;
use Ehimen\Jaslang\Engine\FuncDef\FuncDef;
use Ehimen\Jaslang\Engine\Type\ConcreteType;
use Ehimen\Jaslang\Engine\Type\Type;
use Ehimen\Jaslang\Engine\Type\TypeRepository;
use Ehimen\Jaslang\Engine\Value\Value;
use PHPUnit\Framework\TestCase;

class JaslangInvokerTest extends TestCase
{
    public function testInvokeNoArgs()
    {
        $this->performTest([], [], []);
    }

    public function testInvokeSingleArg()
    {
        $string = $this->createPermissiveType();

        $this->performTest(
            [Parameter::value($string)],
            [$stringValue = $this->createMock(Value::class)],
            [
                ['string', $stringValue, $string],
            ]
        );
    }

    public function testInvokeMultiArgs()
    {
        $string = $this->createPermissiveType();
        $int    = $this->createMock(Type::class);
        
        $this->performTest(
            [Parameter::value($string), Parameter::value($int)],
            [
                $stringValue = $this->createMock(Value::class),
                $intValue    = $this->createMock(Value::class),
            ],
            [
                ['string', $stringValue, $string],
                ['int', $intValue, $int],
            ]
        );
    }

    public function testInvokeOptionalArg()
    {
        $string = $this->createMock(Type::class);
        
        $this->performTest(
            [Parameter::value($string, true)],
            [],
            []
        );
    }

    public function testInvokeMissingArg()
    {
        $string      = $this->createMock(Type::class);
        $stringValue = $this->createMock(Value::class);
        
        $this->performInvalidArgTest(
            [Parameter::value($string)],
            [],
            [
                ['string', $stringValue, $string],
            ],
            InvalidArgumentException::invalidArgument(0, 'string', null)
        );
    }

    public function testInvokeExtraArg()
    {
        $string      = $this->createMock(Type::class);
        $stringValue = $this->createMock(Value::class);
        
        $this->performInvalidArgTest(
            [Parameter::value($string), ],
            [$stringValue, $stringValue],
            [
                ['string', $stringValue, $string],
            ],
            InvalidArgumentException::unexpectedArgument(1)
        );
    }

    public function testInvokeTypeIdentifier()
    {
        $this->performTest(
            [Parameter::type()],
            [$this->createMock(TypeIdentifier::class)],
            []
        );
    }

    public function testInvokeVariable()
    {
        $variable = $this->createMock(Variable::class);
        
        $this->performTest(
            [Parameter::variable()],
            [$variable],
            []
        );
    }

    private function performTest(
        array $parameters,
        array $args,
        array $types
    ) {
        $function   = $this->getFunction(new ArgList($args), $parameters, $context = $this->getContext());
        $repository = $this->getTypeRepository($types);

        $this->getInvoker($repository)->invokeFunction($function, new ArgList($args), $context, $this->getEvaluator());
    }

    private function performInvalidArgTest(
        array $parameters,
        array $args,
        array $types,
        InvalidArgumentException $expected
    ) {
        $function   = $this->getFunction(new ArgList($args), $parameters, $context = $this->getContext(), $this->any());
        $repository = $this->getTypeRepository($types);
        $invoker    = $this->getInvoker($repository);

        try {
            $invoker->invokeFunction($function, new ArgList($args), $context, $this->getEvaluator());
        } catch (InvalidArgumentException $actual) {
            $this->assertEquals($expected, $actual);
            return;
        }
        
        $this->fail(sprintf('Expected test to throw %s, but it didn\'t', InvalidArgumentException::class));
    }

    private function getContext()
    {
        return $this->createMock(EvaluationContext::class);
    }

    private function getInvoker(TypeRepository $typeRepository = null)
    {
        return new JaslangInvoker($typeRepository ? : $this->getTypeRepository());
    }

    private function getTypeRepository(array $types = [])
    {
        $repository = $this->createMock(TypeRepository::class);

        foreach ($types as $type) {
            $repository->method('getTypeName')
                ->with($type[2])
                ->willReturn($type[0]);

            $repository->method('getTypeByValue')
                ->with($type[1])
                ->willReturn($type[2]);
        }

        return $repository;
    }

    /**
     * @return FuncDef
     */
    private function getFunction($expectedArgs, array $parameters, EvaluationContext $context, $invokeCall = null)
    {
        $function = $this->createMock(FuncDef::class);

        $function->expects($invokeCall ?: $this->once())
            ->method('invoke')
            ->with($expectedArgs, $context);

        $function->expects($this->once())
            ->method('getParameters')
            ->willReturn($parameters);

        return $function;
    }

    /**
     * @return Type
     */
    private function createPermissiveType()
    {
        $type = $this->createMock(Type::class);
        
        $type->method('isA')
            ->willReturn(true);
        
        return $type;
    }

    /**
     * @return Evaluator
     */
    private function getEvaluator()
    {
        return $this->createMock(Evaluator::class);
    }
}
