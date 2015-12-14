<?php

namespace Datto\JsonRpc\Simple;

use Datto\JsonRpc\Server;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleEvaluatorWithValidPositionalArguments()
    {
        $evaluator = new Evaluator();

        $server = new Server($evaluator);
        $result = $server->reply('{"jsonrpc": "2.0", "method": "math/subtract", "params": [3, 2], "id": 1}');

        $this->assertSame('{"jsonrpc":"2.0","id":1,"result":1}', $result);
    }

    public function testSimpleEvaluatorWithValidNamedArguments()
    {
        $evaluator = new Evaluator();

        $server = new Server($evaluator);
        $result = $server->reply('{"jsonrpc": "2.0", "method": "math/subtract", "params": {"a": 3, "b": 2}, "id": 1}');

        $this->assertSame('{"jsonrpc":"2.0","id":1,"result":1}', $result);
    }
}
