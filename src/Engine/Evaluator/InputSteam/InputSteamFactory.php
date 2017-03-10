<?php

namespace Ehimen\Jaslang\Engine\Evaluator\InputSteam;

class InputSteamFactory
{
    public function create()
    {
        return new InputStreamStdIn();
    }
}