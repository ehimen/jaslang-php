<?php

namespace Ehimen\JaslangTests;

trait JaslangTestUtil
{
    public function createToken($type, $value, $position)
    {
        return [
            'type' => $type,
            'value' => $value,
            'position' => $position,
        ];
    }
}