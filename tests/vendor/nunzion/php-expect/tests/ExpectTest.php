<?php

/**
 * @author Henning Dieterichs <henning.dieterichs@hediet.de>
 * @copyright (c) 2013-2014, Henning Dieterichs <henning.dieterichs@hediet.de>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Nunzion;

class ExpectTest extends \PHPUnit_Framework_TestCase
{

    public static function getSimpleTests()
    {
        $array1 = array("baz" => "bla");
        $arrayEmpty = array();
        $object1 = (object) array("foo" => "bar");
        $objectStd = new \stdClass();
        $dateTime1 = new \DateTime();

        $callable1 = function ()
        {
            
        };

        $tests = array(
            "isString" => array("str_rot13", "text", "0", "1", ""),
            "isInt" => array(0, 1),
            "isObject" => array($object1, $dateTime1, $objectStd, $callable1),
            "isCallable" => array("str_rot13", $callable1),
            "isArray" => array($array1, $arrayEmpty),
            "isNotNull" => array("str_rot13", "text", "0", "1", "", 0, 1, true, false,
                $callable1, $object1, $objectStd, $array1, $arrayEmpty, $dateTime1),
            "isNotEmpty" => array("str_rot13", "text", "1", 1, true,
                $callable1, $object1, $dateTime1, $array1, $objectStd),
            "isNull" => array(null),
            "isEmpty" => array("", "0", 0, false, null, $arrayEmpty),
            "isNullOrString" => array("str_rot13", "text", "0", "1", "", null),
            "isNullOrInt" => array(0, 1, null),
            "isNullOrObject" => array($object1, $dateTime1, $objectStd, $callable1, null),
        );

        $testCases = array();
        foreach ($tests as $testFunction => $successFullTestCases)
            foreach ($successFullTestCases as $case)
                if (!in_array($case, $testCases, true))
                    $testCases[] = $case;

        $result = array();
        foreach ($tests as $testFunction => $successFullTestCases)
            foreach ($testCases as $testCase)
                $result[] = array($testFunction, $testCase, in_array($testCase, $successFullTestCases, true));

        return $result;
    }

    /**
     * @dataProvider getSimpleTests
     */
    public function testThat($condition, $var, $successFullExpectation)
    {
        if (!$successFullExpectation)
            $this->setExpectedException("\InvalidArgumentException");
        Expect::that($var)->$condition();
    }

    public static function getInstanceOfTests()
    {
        return array(
            array(new \DateTime(), "\DateTime", true),
            array(new \stdClass(), "\stdClass", true),
            array(new \ArrayObject(), "\ArrayObject", true),
            array(new \ArrayObject(), "\ArrayAccess", true),
            array("string", "string", false),
            array(null, "\DateTime", false),
            array(new \stdClass(), "\DateTime", false)
        );
    }

    /**
     * @dataProvider getInstanceOfTests
     */
    public function testInstanceOf($var, $typeOrInterface, $successFullExpectation)
    {
        if (!$successFullExpectation)
            $this->setExpectedException("\InvalidArgumentException");
        Expect::that($var)->isInstanceOf($typeOrInterface);
    }
    
    public function testIs1()
    {
        Expect::that(1)->is("int");
        Expect::that(1.0)->is("float");
        Expect::that(new \Exception())->is("\Exception");
    }
    
    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage The value must be type of 'float', but is type of 'Exception'.
     */
    public function testIs2()
    {
        Expect::that(new \Exception())->is("float");
    }
    
    
    public function testArrayOf1()
    {
        $arr1 = array(1, 2, 3);
        Expect::that($arr1)->isArrayOf("int");
        
        $arr2 = array(new \InvalidArgumentException(), new \Exception());
        Expect::that($arr2)->isArrayOf("\Exception");
    }
    
    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage '$arr[0]' must be type of 'int', but is type of 'InvalidArgumentException'.
     */
    public function testArrayOf2()
    {
        $arr = array(new \InvalidArgumentException(), new \Exception());
        Expect::that($arr)->isArrayOf("int");
    }
    
    /**
     * @expectedException \UnexpectedValueException
     */
    public function testAnonymousFunction()
    {
        $test = function ($parameter)
        {
            Expect::that($parameter)->isNull();
        };

        $test(5);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage The value must be valid.
     */
    public function testUnexpectedValueException()
    {
        Expect::that("value")->_("must be valid");
    }

    private function validateArgument($arg)
    {
        Expect::that($arg)->_("must be valid");
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameter 'arg' must be valid.
     */
    public function testInvalidArgumentException()
    {
        $this->validateArgument("foo");
    }

    private function deepValidateArgument($arg)
    {
        Expect::that($arg["test"])->_("has to be valid");
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameter 'arg' is invalid: '$arg["test"]' has to be valid.
     */
    public function testDeepInvalidArgumentException()
    {
        $this->deepValidateArgument(array("test" => "foo"));
    }

    private $objectValue;

    /**
     * @expectedException Nunzion\InvariantViolationException
     * @expectedExceptionMessage Member 'objectValue' must be valid.
     */
    public function testInvariantViolationException()
    {
        Expect::that($this->objectValue)->_("must be valid");
    }

    /**
     * @expectedException Nunzion\InvariantViolationException
     * @expectedExceptionMessage Member 'objectValue' is invalid: '$this->objectValue->bar' must be valid.
     */
    public function testDeepInvariantViolationException()
    {
        $this->objectValue = (object) array("bar" => "foo");
        Expect::that($this->objectValue->bar)->_("must be valid");
    }

    /**
     * @expectedException Nunzion\InvariantViolationException
     * @expectedExceptionMessage Member 'objectValue' is invalid: '$this->objectValue[bar1]' must be type of 'string', but is undefined.
     */
    public function testItsArrayElement1()
    {
        $this->objectValue = array("bar" => "foo");
        Expect::that($this->objectValue)->itsArrayElement("bar1")->isString();
    }

    /**
     * @expectedException Nunzion\InvariantViolationException
     * @expectedExceptionMessage Member 'objectValue' must be type of 'array', but is null.
     */
    public function testItsArrayElement2()
    {
        $this->objectValue = null;
        Expect::that($this->objectValue)->itsArrayElement("bar1")->isString();
    }

    /**
     * @expectedException Nunzion\InvariantViolationException
     * @expectedExceptionMessage Member 'objectValue' is invalid: '$this->objectValue[bar1]' must be type of 'string', but is undefined.
     */
    public function testItsArrayElement3()
    {
        $this->objectValue = array("bar" => "foo");
        Expect::that($this->objectValue)->itsArrayElement("bar1")->isNullOrString();
    }

    public static function getIntTests()
    {
        return array(
            array(1, "isGreaterThan", 0, true),
            array(1, "isGreaterThan", 1, false),
            array(1, "isGreaterThan", 2, false),
            array(1, "isLessThan", 0, false),
            array(1, "isLessThan", 1, false),
            array(1, "isLessThan", 2, true),
            array(1, "isGreaterOrEqualThan", 0, true),
            array(1, "isGreaterOrEqualThan", 1, true),
            array(1, "isGreaterOrEqualThan", 2, false),
            array(1, "isLessOrEqualThan", 0, false),
            array(1, "isLessOrEqualThan", 1, true),
            array(1, "isLessOrEqualThan", 2, true)
        );
    }

    /**
     * @dataProvider getIntTests
     */
    public function testIntMethods($var, $method, $arg1, $successFullExpectation)
    {
        if (!$successFullExpectation)
            $this->setExpectedException("\InvalidArgumentException");
        Expect::that($var)->$method($arg1);
    }

    public function testIsBetween1()
    {
        Expect::that(5)->isBetween(4, 6);
        Expect::that(5)->isBetween(5, 6);
        Expect::that(5)->isBetween(5, 5);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage The value must be between 4 and 6, but is 7.
     */
    public function testIsBetween2()
    {
        Expect::that(7)->isBetween(4, 6);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage The value must be between 4 and 6, but is 3.
     */
    public function testIsBetween3()
    {
        Expect::that(3)->isBetween(4, 6);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage The value must be valid, the test returned: Some value.
     */
    public function testExample()
    {
        Expect::that(null)->isNull();
        Expect::that(0)->isNotNull(); // 0 !== null, so 0 is not null.
        Expect::that("")->isEmpty();
        Expect::that(2)->isNotEmpty();

        Expect::that("foo")->equals("foo"); //"foo" == "foo"
        Expect::that(1)->isTheSameAs(1); //1 === 1

        Expect::that("bar")->isString();
        Expect::that(4)->isInt();
        Expect::that(new \stdClass())->isObject();
        Expect::that("file_exists")->isCallable();
        Expect::that(array(1, 2))->isArray();

        Expect::that(1)->isBetween(0, 2)
                       ->isGreaterThan(0)
                       ->isLessThan(2)
                       ->isGreaterThanOrEqualTo(1)
                       ->isLessThanOrEqualTo(1);

        $arr = array("bla" => 2, "foo" => null);
        Expect::that($arr)->itsArrayElement("bla")->isDefined();
        Expect::that($arr)->itsArrayElement("bla")->isInt();
        Expect::that($arr)->itsArrayElement("blubb")->isUndefinedOrInt();
        Expect::that($arr)->itsArrayElement("foo")->isNullOrInt();

        //Will throw an \UnexpectedValueException with message:
        //The value must be valid, the test returned: Some value.
        Expect::that(null)->_("must be valid, the test returned: {testResult}", array("testResult" => "Some value"));
    }

    public function testFluent()
    {
        Expect::that(1)
                ->isTheSameAs(1)
                ->isBetween(0, 2)
                ->isGreaterThan(0)
                ->isLessThan(2)
                ->isGreaterOrEqualThan(1)
                ->isNotNull()
                ->isLessOrEqualThan(1);

        $arr = array("bla" => 4, "foo" => null);
        Expect::that($arr)
                ->itsArrayElement("bla")
                ->isDefined()
                ->isUndefinedOrInt()
                ->isInt();
    }

    /**
     * @expectedException Nunzion\InvariantViolationException
     * @expectedExceptionMessage Member 'objectValue' must be less or equal than 0, but is 1.
     */
    public function testParameterExtractionIfThereWereMultipleLines()
    {
        $this->objectValue = 1;

        Expect::that($this->objectValue)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(0);
    }

    /**
     * @expectedException Nunzion\InvariantViolationException
     * @expectedExceptionMessage Member 'objectValue' must be less or equal than 0, but is 1.
     */
    public function testCustomExpect()
    {
        $this->objectValue = 1;

        CustomExpect::that($this->objectValue)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(1)
                ->isLessThanOrEqualTo(0);
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Parameter 'length' must be greater than 0, but is -1.
     */
    public function testSquare1() 
    {
        new Square(-1);
    }

    /**
     * @expectedException Nunzion\InvariantViolationException
     * @expectedExceptionMessage Member 'b' must be equal to 2, but is 1.
     */
    public function testSquare2() 
    {
        $s = new Square(2);
        $s->getArea();
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameter 'data' is invalid: '$data[length]' must be type of 'number', but is undefined.
     */
    public function testSquare3() 
    {
        $s = new Square(2);
        $s->setData(array());
    }
    
    public function testSquare4() 
    {
        $s = new Square(2);
        $s->setData(array("length" => 2));
    }
}

class Square
{
    private $a;
    private $b;

    public function __construct($length)
    {
        //throws \InvalidArgumentException,
        //because "length" is a parameter of Square::__construct
        Expect::that($length)->isGreaterThan(0);

        $this->a = $length;
        $this->b = $length - 1; //an invariant violation.
    }

    public function getArea()
    {
        //Throws Nunzion\InvariantException,
        //because "b" is a member of Square.
        Expect::that($this->b)->equals($this->a); 

        return $this->a * $this->b;
    }

    public function setData($data)
    {
        //Expects data is array and has a key 'length'
        //which is int and greater than 0
        Expect::that($data)->itsArrayElement("length")->isGreaterThan(0);
    }
}

class CustomExpect extends \Nunzion\Expect
{
    
}
