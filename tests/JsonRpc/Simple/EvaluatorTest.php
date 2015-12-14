<?php

namespace Datto\JsonRpc\Simple;

use Datto\JsonRpc\Server;

class EvaluatorTest extends \PHPUnit_Framework_TestCase
{
    public function testEvaluatorWithPositionalArguments()
    {
        $evaluator = new Evaluator();
        $result = $evaluator->evaluate('math/subtract', array(3, 2));

        $this->assertSame(1, $result);
    }

    public function testEvaluatorWithNamedArguments()
    {
        $evaluator = new Evaluator();
        $result = $evaluator->evaluate('math/subtract', array('b' => 2, 'a' => 3));

        $this->assertSame(1, $result);
    }

    public function testEvaluatorWithNamedOptionalArgumentsWithoutOptional()
    {
        $evaluator = new Evaluator();
        $result = $evaluator->evaluate('math/pow', array('a' => 3));

        $this->assertSame(9, $result);
    }

    public function testEvaluatorWithNamedOptionalArgumentsWithOptional()
    {
        $evaluator = new Evaluator();
        $result = $evaluator->evaluate('math/pow', array('a' => 3, 'b' => 3));

        $this->assertSame(27, $result);
    }

    public function testEvaluatorWithPositionalOptionalArgumentsWithoutOptional()
    {
        $evaluator = new Evaluator();
        $result = $evaluator->evaluate('math/pow', array(3));

        $this->assertSame(9, $result);
    }

    public function testEvaluatorWithPositionalOptionalArgumentsWithOptional()
    {
        $evaluator = new Evaluator();
        $result = $evaluator->evaluate('math/pow', array(3, 3));

        $this->assertSame(27, $result);
    }

    public function testEvaluatorWithCustomNamespace()
    {
        $evaluator = new Evaluator(new Mapper('Datto\\Test'));
        $result = $evaluator->evaluate('math/add', array(3, 3));

        $this->assertSame(6, $result);
    }

    public function testEvaluatorWithCustomMapper()
    {
        $evaluator = new Evaluator(new StaticMathMapper());
        $result = $evaluator->evaluate('multiply', array(3, 3));

        $this->assertSame(9, $result);
    }

    public function testEvaluatorWithTypeHintedParamInEndpoint()
    {
        $evaluator = new Evaluator(new Mapper('Datto\\Test'));
        $result = $evaluator->evaluate('offsite/getTargetType', array('identifier' => 'mac{aabbccddeeff}'));

        $this->assertSame('deviceID=dummy, mac=aabbccddeeff', $result);
    }

    /**
     * @expectedException \Datto\JsonRpc\Exception\Evaluation
     */
    public function testEvaluationWithEvaluationException()
    {
        $evaluator = new Evaluator();
        $evaluator->evaluate('math/divide', array(1, 0));
    }

    /**
     * @expectedException \Datto\JsonRpc\Exception\NotSupported
     */
    public function testEvaluatorWithCustomException()
    {
        $evaluator = new Evaluator();
        $evaluator->evaluate('math/add', array(1, 2));
    }

    /**
     * @expectedException \Datto\JsonRpc\Exception\Method
     */
    public function testEvaluatorWithNoClassName()
    {
        $evaluator = new Evaluator();
        $evaluator->evaluate('subtract', array(3, 2));
    }

    /**
     * @expectedException \Datto\JsonRpc\Exception\Method
     */
    public function testEvaluatorWithInvalidClass()
    {
        $evaluator = new Evaluator();
        $evaluator->evaluate('INVALID/subtract', array('b' => 2, 'a' => 3));
    }

    /**
     * @expectedException \Datto\JsonRpc\Exception\Method
     */
    public function testEvaluatorWithInvalidMethod()
    {
        $evaluator = new Evaluator();
        $evaluator->evaluate('math/INVALID', array('b' => 2, 'a' => 3));
    }

    /**
     * @expectedException \Datto\JsonRpc\Exception\Argument
     */
    public function testEvaluatorWithNamedArgumentMissingAndInvalid()
    {
        $evaluator = new Evaluator();
        $evaluator->evaluate('math/subtract', array('INVALID' => 2, 'a' => 3));
    }

    /**
     * @expectedException \Datto\JsonRpc\Exception\Argument
     */
    public function testEvaluatorWithNamedArgumentMissing()
    {
        $evaluator = new Evaluator();
        $evaluator->evaluate('math/subtract', array('a' => 3));
    }

    /**
     * @expectedException \Datto\JsonRpc\Exception\Argument
     */
    public function testEvaluatorWithPositionalArgumentMissing()
    {
        $evaluator = new Evaluator();
        $evaluator->evaluate('math/subtract', array(3));
    }

    /**
     * @expectedException \Datto\JsonRpc\Exception\Method
     */
    public function testEvaluatorWithInvalidEndpoint()
    {
        $evaluator = new Evaluator();
        $evaluator->evaluate('illegal/robBank', array());
    }
}
