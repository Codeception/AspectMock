<?php
namespace AspectMock\Proxy;
use \PHPUnit_Framework_ExpectationFailedException as fail;
use AspectMock\Util\ArgumentsFormatter;

/**
 * Interface `Verifiable` defines methods to verify method calls.
 * Implementation may differ for class methods and instance methods.
 *   
 */

abstract class Verifier {

    /**
     *  Name of a class.
     */
    public $className;

    protected $invokedFail = "Expected %s to be invoked but it never occur.";
    protected $notInvokedMultipleTimesFail = "Expected %s to be invoked %s times but it never occur.";
    protected $invokedMultipleTimesFail = "Expected %s to be invoked but called %s times but called %s.";

    protected $neverInvoked = "Expected %s not to be invoked but it was.";

    abstract public function getCallsForMethod($method);

    protected function callSyntax($method)
    {
        return method_exists($this->className,$method)
            ? '::'
            : '->';
    }

    protected function onlyExpectedArguments($expectedParams, $passedArgs)
    {
        return empty($expectedParams) ?
            $passedArgs :
            array_slice($passedArgs, 0, count($expectedParams));
    }

    /**
     * Verifies a method was invoked at least once.
     * In second argument you can specify with which params method expected to be invoked;
     *
     * ``` php
     * <?php
     * $user->verifyInvoked('save');
     * $user->verifyInvoked('setName',['davert']);
     *
     * ?>
     * ```
     *
     * @param $name
     * @param null $params
     * @throws \PHPUnit_Framework_ExpectationFailedException
     * @param array $params
     * @throws fail
     */
    public function verifyInvoked($name, $params = null)
    {
        $calls = $this->getCallsForMethod($name);
        $separator = $this->callSyntax($name);

        if (empty($calls)) throw new fail(sprintf($this->invokedFail, $this->className.$separator.$name));

        if (is_array($params)) {
            foreach ($calls as $args) {
                if ($this->onlyExpectedArguments($params, $args) === $params) return;
            }
            $params = ArgumentsFormatter::toString($params);
            throw new fail(sprintf($this->invokedFail, $this->className.$separator.$name."($params)"));
        } else if(is_callable($params)) {
            $params($calls);
        }
    }

    /**
     * Verifies that method was invoked only once.
     *
     * @param $name
     * @param array $params
     */
    public function verifyInvokedOnce($name, $params = null)
    {
        $this->verifyInvokedMultipleTimes($name, 1, $params);
    }

    /**
     * Verifies that method was called exactly $times times.
     *
     * ``` php
     * <?php
     * $user->verifyInvokedMultipleTimes('save',2);
     * $user->verifyInvokedMultipleTimes('dispatchEvent',3,['before_validate']);
     * $user->verifyInvokedMultipleTimes('dispatchEvent',4,['after_save']);
     * ?>
     * ```
     *
     * @param $name
     * @param $times
     * @param array $params
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function verifyInvokedMultipleTimes($name, $times, $params = null)
    {
        if ($times == 0) return $this->verifyNeverInvoked($name, $params);

        $calls = $this->getCallsForMethod($name);
        $separator = $this->callSyntax($name);

        if (empty($calls)) throw new fail(sprintf($this->notInvokedMultipleTimesFail, $this->className.$separator.$name, $times));
        if (is_array($params)) {
            $equals = 0;
            foreach ($calls as $args) {
                if ($this->onlyExpectedArguments($params, $args) == $params) $equals++;
            }
            if ($equals == $times) return;
            $params = ArgumentsFormatter::toString($params);
            throw new fail(sprintf($this->invokedMultipleTimesFail, $this->className.$separator.$name."($params)", $times, $equals));
        } else if(is_callable($params)) {
            $params($calls);
        }
        $num_calls = count($calls);
        if ($num_calls != $times) throw new fail(sprintf($this->invokedMultipleTimesFail, $this->className.$separator.$name, $times, $num_calls));
    }

    /**
     * Verifies that method was not called.
     * In second argument with which arguments is not expected to be called.
     *
     * ``` php
     * <?php
     * $user->setName('davert');
     * $user->verifyNeverInvoked('setName'); // fail
     * $user->verifyNeverInvoked('setName',['davert']); // fail
     * $user->verifyNeverInvoked('setName',['bob']); // success
     * $user->verifyNeverInvoked('setName',[]); // success
     * ?>
     * ```
     *
     * @param $name
     * @param null $params
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function verifyNeverInvoked($name, $params = null)
    {
        $calls = $this->getCallsForMethod($name);
        $separator = $this->callSyntax($name);

         if (is_array($params)) {
             if (empty($calls)) return;
             $params = ArgumentsFormatter::toString($params);
             foreach ($calls as $args) {
                 if ($this->onlyExpectedArguments($params, $args) == $params) throw new fail(sprintf($this->neverInvoked, $this->className));
             }
             return;
         }
         if (count($calls)) throw new fail(sprintf($this->neverInvoked, $this->className.$separator.$name));        
    }

}
