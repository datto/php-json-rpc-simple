<?php

namespace Datto\API;

class Illegal
{
    public function __construct($illegal)
    {
        // We don't support arguments in the constructor!
    }

    public function robBank()
    {
        return true;
    }
}