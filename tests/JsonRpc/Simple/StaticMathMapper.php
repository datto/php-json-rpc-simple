<?php

namespace Datto\JsonRpc\Simple;

use Datto\API\Math;
use Datto\JsonRpc;

class StaticMathMapper implements JsonRpc\Mapper
{
    public function getCallable($methodName)
    {
        return array(new Math(), 'multiply');
    }

    public function getArguments($callable, $arguments)
    {
        return array_values($arguments);
    }
}