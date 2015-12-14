<?php

namespace Datto\JsonRpc;

/**
 * Maps JSON-RPC arguments 'method' to a callable and expands/reorders the 'arguments' array to match
 * the method's definition.
 *
 * @author Philipp Heckel <ph@datto.com>
 */
interface Mapper
{
    /**
     * Maps JSON-RPC method name to a PHP callable function.
     *
     * @param string $methodName JSON-RPC method name
     * @return callable Returns a callable method
     * @throws Exception\Method If the method does not map to a valid callable
     */
    public function getCallable($methodName);

    /**
     * Fill the arguments for the given callable (with optional values) and
     * optionally order them to match the method signature (associative arrays only).
     *
     * @param callable $callable The callable to be used to inspect the parameters
     * @param array $arguments Array of arguments
     * @return mixed the return value from the callable.
     * @throws Exception If arguments are invalid or execution fails
     */
    public function getArguments($callable, $arguments);
}
