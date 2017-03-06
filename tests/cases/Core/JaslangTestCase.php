<?php

namespace Ehimen\JaslangTests\Core;

use Ehimen\Jaslang\Core\JaslangFactory;

trait JaslangTestCase
{
    private function getInterpreter()
    {
        return (new JaslangFactory())->create();
    }
}
