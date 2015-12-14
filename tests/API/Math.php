<?php

namespace Datto\API;

use Datto\JsonRpc;

class Math
{
    public function subtract($a, $b)
    {
        return $a - $b;
    }

    public function pow($a, $b = 2)
    {
        return pow($a, $b);
    }

    public function divide($a, $b)
    {
        if ($b === 0) {
            throw new \Exception('Division by zero.');
        }

        return $a / $b;
    }

    public function add($a, $b)
    {
        throw new JsonRpc\Exception\NotSupported();
    }

    public function multiply($a, $b)
    {
        return $a * $b;
    }
}