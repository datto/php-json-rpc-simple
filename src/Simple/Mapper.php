<?php

namespace Datto\JsonRpc\Simple;

use Exception;
use InvalidArgumentException;
use ReflectionMethod;
use ReflectionParameter;
use Datto\JsonRpc;

/**
 * Simple implementation of the JsonRpc\Mapper interface.
 *
 * This implementation maps the JSON-RPC 'method' to a callable and then
 * executes it by mapping the JSON-RPC 'params' to its method arguments.
 *
 * Method mapping:
 *   The default API endpoints are located in the Datto\API\* namespace. Every public
 *   function in these endpoints can be called through the API, e.g.
 *
 *       v1/device/ownCloud/getStatus -> (new \Datto\API\V1\Device\OwnCloud())->getStatus()
 *
 * Parameter mapping:
 *   Method arguments are automatically mapped, regardless or whether a positional array
 *   or an associative array was passed. If a callable has optional parameters, they are
 *   filled with their optional values:
 *
 *      {.. "method": "v1/device/ownCloud/setDomainAlias", "params": {"alias": "own.a.com"} ..}
 *   -> (new \Datto\API\V1\Device\OwnCloud())->setDomainAlias("own.a.com")
 *
 *      {.. "method": "v1/device/ownCloud/setDomainAlias", "params": ["own.a.com"] ..}
 *   -> (new \Datto\API\V1\Device\OwnCloud())->setDomainAlias("own.a.com")
 *
 * @author Philipp Heckel <ph@datto.com>
 */
class Mapper implements JsonRpc\Mapper
{
    /** Default namespace to be used if no custom endpoint is provided in the constructor */
    const DEFAULT_NAMESPACE = '\\Datto\\API\\';

    /** @var string Root namespace to use for endpoint classes/methods */
    private $namespace;

    /** @var string Namespace/class/method separator */
    private $separator;

    /**
     * Creates a simple mapper. If no arguments are provided, the default namespace
     * is used to map the JSON-RPC method name to an API class/method.
     *
     * @param string $namespace Root namespace to use for endpoint mapping
     */
    public function __construct($namespace = self::DEFAULT_NAMESPACE, $separator = '/')
    {
        $this->namespace = '\\' . trim($namespace, '\\') . '\\';
        $this->separator = $separator;

        if (preg_match('~^[[:alnum:]]$~', $separator) || strlen($separator) !== 1) {
            throw new InvalidArgumentException('Invalid separator, must be one-char and not alphanumerical');
        }
    }

    /**
     * Maps JSON-RPC method name to a PHP callable function.
     *
     * @param string $methodName JSON-RPC method name
     * @return callable Returns a callable method
     * @throws JsonRpc\Exception\Method If the method does not map to a valid callable
     */
    public function getCallable($methodName)
    {
        $this->checkMethodName($methodName);

        $class = $this->getClassName($methodName);
        $method = $this->getMethodName($methodName);

        $object = $this->createObject($class);

        if (!method_exists($object, $method)) {
            throw new JsonRpc\Exception\Method();
        }

        return array($object, $method);
    }

    /**
     * Fill the arguments for the given callable (with optional values) and
     * optionally order them to match the method signature (associative arrays only).
     *
     * @param callable $callable The callable to be used to inspect the parameters
     * @param array $arguments Array of arguments
     * @return mixed the return value from the callable.
     * @throws JsonRpc\Exception If arguments are invalid or execution fails
     */
    public function getArguments($callable, $arguments)
    {
        if ($this->isPositionalArguments($arguments)) {
            return $this->orderAndFillArguments($callable, $arguments, false);
        } else {
            return $this->orderAndFillArguments($callable, $arguments, true);
        }
    }

    /**
     * Extracts the class name from the JSON-RPC method name,
     * e.g. v1/device/ownCloud/getStatus -> \Datto\API\V1\Device\OwnCloud
     *
     * @param string $methodName JSON-RPC method name
     * @return string PHP fully qualified class name
     */
    private function getClassName($methodName)
    {
        $parts = explode($this->separator, $methodName);
        array_pop($parts); // Remove method

        foreach ($parts as &$part) {
            $part = ucwords($part);
        }

        return $this->namespace . join('\\', $parts);
    }

    /**
     * Extracts the method name from the JSON-RPC method name,
     * e.g. device/ownCloud/getStatus -> getStatus
     *
     * @param string $methodName JSON-RPC method name
     * @return string PHP method name
     */
    private function getMethodName($methodName)
    {
        $parts = explode($this->separator, $methodName);
        return array_pop($parts);
    }

    /**
     * Validates the method name for valid/invalid characters
     *
     * @param string $methodName JSON-RPC method name
     * @throws JsonRpc\Exception\Method If the method name in invalid
     */
    private function checkMethodName($methodName)
    {
        $methodRegex = '~^[[:alnum:]]+(\\' . $this->separator . '[[:alnum:]]+)+$~';

        if (preg_match($methodRegex, $methodName) !== 1) {
            throw new JsonRpc\Exception\Method();
        }
    }

    /**
     * Create an object from the given class name
     *
     * @param string $class Fully qualified class name
     * @return object Object of the type 'class'
     * @throws JsonRpc\Exception\Method If the class does not exist or the class creation fails
     */
    private function createObject($class)
    {
        if (!class_exists($class)) {
            throw new JsonRpc\Exception\Method();
        }

        try {
            return new $class();
        } catch (Exception $e) {
            throw new JsonRpc\Exception\Method();
        }
    }

    /**
     * Returns true if the argument array is a zero-indexed list of positional
     * arguments, or false if the argument array is a set of named arguments.
     *
     * @param array $arguments Array of arguments.
     * @return bool Returns true iff the arguments array is zero-indexed.
     */
    private function isPositionalArguments($arguments)
    {
        $i = 0;
        foreach ($arguments as $key => $value) {
            if ($key !== $i++) {
                return false;
            }
        }
        return true;
    }

    /**
     * Orders the given argument list to match the callable/method arguments (to
     * allow auto-mapping), and fill resulting array with default parameter values
     * for optional parameters.
     *
     * @param callable $callable A callable to be used to detect the argument names.
     * @param array $arguments Array of arguments; keys will be used to match parameters.
     * @return array Returns an index-based array or arguments.
     * @throws JsonRpc\Exception\Argument If arguments are invalid or missing
     */
    private function orderAndFillArguments($callable, $arguments, $indexByName)
    {
        $method = new ReflectionMethod($callable[0], $callable[1]);
        $filledArguments = array();

        foreach ($method->getParameters() as $param) {
            $index = ($indexByName) ? $param->getName() : $param->getPosition();

            if (isset($arguments[$index])) {
                $filledArguments[] = $this->fillArgument($param, $arguments[$index]);
            } else if ($param->isOptional()) {
                $filledArguments[] = $this->fillArgument($param, $param->getDefaultValue());
            } else {
                throw new JsonRpc\Exception\Argument();
            }
        }

        return $filledArguments;
    }

    /**
     * Returns the final value of the argument, i.e. either the raw value (as it was passed),
     * or the object that the parameter relates to.
     *
     * This method creates an object from the raw value of the parameter if the method argument
     * was typehinted.
     *
     * Example: If an endpoint 'function getType(DeviceIdentifier $id)' has a typehinted
     * argument, this method will expand the raw value of 'id' (here: $value) by creating a
     * new instance of DeviceIdentifier via 'new DeviceIdentifier($value)'.
     *
     * @param ReflectionParameter $param Reflection parameter
     * @param mixed $value Raw value of the parameter
     * @return mixed Expanded value of the parameter
     * @throws JsonRpc\Exception\Argument If the typehinted class doesn't exist, or the object cannot be created
     */
    private function fillArgument(ReflectionParameter $param, $value)
    {
        try {
            $isTypedParam = !is_null($param->getClass());

            if (!$isTypedParam) {
                return $value;
            } else {
                $class = $param->getClass()->getName();

                if (class_exists($class)) {
                    return new $class($value);
                } else {
                    throw new JsonRpc\Exception\Argument();
                }
            }
        } catch (Exception $e) {
            throw new JsonRpc\Exception\Argument();
        }
    }
}
