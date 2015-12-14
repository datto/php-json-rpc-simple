<?php

namespace Datto\JsonRpc\Exception;

class NotSupported extends \Exception implements \Datto\JsonRpc\Exception
{
    public function __construct()
    {
        parent::__construct('Not supported.', -32001);
    }
}

