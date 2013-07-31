<?php
namespace AspectMock\Proxy;
use AspectMock\Core\Registry;
use PHPUnit_Framework_Assert as a;


/**
 * Is created by a call to `verifyInvoked*` method of ClassProxy or InstanceProxy.
 * Used to check the results of method invocation.
 *
 * ``` php
 * <?php
 * $userClass = test::double('User');
 * $table = User::getTableName();
 * $userClass->verifyInvoked('getTableName')->returned($table);
 * ?>
 * ```
 *
 * Class MethodProxy
 * @package AspectMock\Proxy
 */
class MethodProxy {
    
    protected $values = [];
    protected $signature;

    public function __construct($classOrInstance, $method)
    {
        $this->values = Registry::getReturnedValues($classOrInstance, $method);
        $class = is_object($classOrInstance)
            ? get_class($classOrInstance)
            : $classOrInstance;
        $this->signature = "$class->$method()";
    }

    /**
     * Verifies that method that was invoked returned specified result at least once.
     *
     * ``` php
     * <?php
     * $user->name = 'davert';
     * $user->getName();
     * $user->name = 'jon';
     * $user->getName();
     * $user->verifyInvoked('getName')->returned('davert');
     * ?>
     * ```
     *
     * @param $result
     */
    public function returned($result)
    {
        a::assertContains($result, $this->values, "{$this->signature} returned the expected result at least once");
    }

    /**
     * Verifies that invoked method returned the specified result only once.
     *
     * @see returnedMultipleTimes
     * @param $result
     */
    public function returnedOnce($result)
    {
        $this->returnedMultipleTimes($result, 1);
    }

    /**
     * Checks that invoked method returned provided value exactly $times.
     *
     * ``` php
     * <?php
     * $user->name = 'davert';
     * $user->getName();
     * $user->name = 'jon';
     * $user->getName();
     * $user->getName();
     * $user->verifyInvoked('getName')->returnedOnce('davert');
     * $user->verifyInvoked('getName')->returnedMultipleTimes('jon', 2);
     * ?>
     * ```
     *
     * @param $result
     * @param $times
     */
    public function returnedMultipleTimes($result, $times)
    {
        $values = array_count_values($this->values);
        if (!isset($values[$result])) a::fail("{$this->signature} returned the expected result at least once");

        a::assertEquals($times, $values[$result], "{$this->signature} returned the expected result $times times");
    }

    /**
     * Verifies that call to a method never returned a value provided.
     *
     * ``` php
     * <?php
     * $user->name = 'davert';
     * $user->getName();
     * $user->verifyInvoked('getName')->neverReturned('jon');
     * ?>
     * ```
     *
     * @param $result
     */
    public function neverReturned($result)
    {
        a::assertNotContains($result, $this->values, "{$this->signature} never returned the expected result");
    }

    /**
     * Returns an array with all the result of all method invocations.
     *
     * @return array
     */
    public function results()
    {
        return $this->values;
    }

    /**
     * Returns the result of last method invocation.
     *
     * @return mixed
     */
    public function result()
    {

        return end($this->values);
    }

}