<?php

namespace Ehimen\JaslangTests\Core;

use Ehimen\Jaslang\Core\JaslangFactory;
use Ehimen\Jaslang\Engine\Evaluator\Context\JaslangContextFactory;
use Ehimen\Jaslang\Engine\Evaluator\InputSteam\InputSteamFactory;
use Ehimen\Jaslang\Engine\Evaluator\InputSteam\InputStream;
use Ehimen\Jaslang\Engine\Type\TypeRepository;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 */
trait JaslangTestCase
{
    private function getInterpreter($input)
    {
        $typeRepo = new TypeRepository();

        return (new JaslangFactory(
            new JaslangContextFactory($typeRepo, $this->getMockInputStreamFactory($input)),
            $typeRepo
        ))->create();
    }

    /**
     * @return \Ehimen\Jaslang\Engine\Evaluator\InputSteam\InputSteamFactory
     */
    private function getMockInputStreamFactory(array $input)
    {
        $mock = $this->createMock(InputSteamFactory::class);

        $mock->method('create')
            ->willReturn($this->getMockInputStream($input));

        return $mock;
    }

    /**
     * @return \Ehimen\Jaslang\Engine\Evaluator\InputSteam\InputStream
     */
    private function getMockInputStream(array $input)
    {
        $mock = $this->createMock(InputStream::class);

        $mock->method('consume')
            ->willReturnCallback(function () use (&$input) {
                $chunk = current($input);
                next($input);
                return $chunk;
            });

        return $mock;
    }
}
