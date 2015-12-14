<?php

namespace Datto\JsonRpc\Simple;

use Exception;
use Datto\JsonRpc;

/**
 * Simple implementation of the JsonRpc\Evaluator interface.
 *
 * This implementation uses a JsonRpc\Mapper to map the JSON-RPC method and arguments
 * to a callable. The method returned by the mapper is then simply executed.
 *
 * @author Philipp Heckel <ph@datto.com>
 */
class Evaluator implements JsonRpc\Evaluator
{
    /** @var JsonRpc\Mapper */
    private $mapper;

    /**
     * Creates a simple evaluator. If no arguments are provided, the default namespace
     * is used to map the JSON-RPC method name to an API class/method.
     *
     * @param string $namespace Root namespace to use for endpoint mapping
     */
    public function __construct(JsonRpc\Mapper $mapper = null)
    {
        $this->mapper = ($mapper) ? $mapper : new Mapper();
    }

    /**
     * Map method name to callable and run it with the given arguments.
     *
     * @param string $method Method name
     * @param array $arguments Positional or associative argument array
     * @return mixed Return value of the callable
     */
    public function evaluate($method, $arguments = array())
    {
        $callable = $this->mapper->getCallable($method);
        $arguments = $this->mapper->getArguments($callable, $arguments);

        return $this->execute($callable, $arguments);
    }

    /**
     * Executes the given callable with the arguments provided.
     *
     * If the callable throws an Exception, it is wrapped into a
     * JsonRpc\Exception\Evaluation object if necessary.
     *
     * @param callable $callable A callable to be used to detect the argument names.
     * @param array $arguments Array of arguments to be passed to the callable
     * @return mixed Return value of the callable
     * @throws JsonRpc\Exception If the callable throws an exception
     */
    private function execute($callable, $arguments)
    {
        try {
            return call_user_func_array($callable, $arguments);
        } catch (Exception $e) {
            if ($e instanceof JsonRpc\Exception) {
                throw $e;
            } else {
                throw new JsonRpc\Exception\Evaluation($e->getMessage(), $e->getCode());
            }
        }
    }
}
