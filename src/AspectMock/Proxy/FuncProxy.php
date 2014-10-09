<?php
namespace AspectMock\Proxy;

use AspectMock\Core\Registry;

/**
 * FuncProxy is a wrapper around mocked function, used to verify function calls.
 * Has the same verification methods as `InstanceProxy` and `ClassProxy` do.
 *
 * Usage:
 *
 * ```php
 * <?php
 * namespace Acme\User;
 * $func = test::func('Acme\User', 'strlen', 10);
 * strlen('hello');
 * strlen('world');
 * $func->verifyInvoked(); // true
 * $func->verifyInvoked(['hello']); // true
 * $func->verifyInvokedMultipleTimes(2);
 * $func->verifyNeverInvoked(['bye']);
 *
 * ```
 *
 */
class FuncProxy extends Verifier
{
    protected $func;
    protected $ns;
    protected $fullFuncName;

    public function __construct($namespace, $func)
    {
        $this->func = $func;
        $this->ns = $namespace;
        $this->fullFuncName = $namespace . '/' . $func;
    }

    protected function callSyntax($method)
    {
        return "";
    }

    /**
     * @param null $params
     */
    public function verifyInvoked($params = null)
    {
        parent::verifyInvoked($this->func, $params);
    }

    /**
     * @param null $params
     */
    public function verifyInvokedOnce($params = null)
    {
        $this->verifyInvokedMultipleTimes(1, $params);
    }

    /**
     * @param null $params
     */
    public function verifyNeverInvoked($params = null)
    {
        parent::verifyNeverInvoked($this->func, $params);
    }

    /**
     * @param $times
     * @param null $params
     */
    public function verifyInvokedMultipleTimes($times, $params = null)
    {
        parent::verifyInvokedMultipleTimes($this->func, $times, $params);
    }

    /**
     * Executes mocked function with provided parameters.
     * @return mixed
     */
    public function __invoke()
    {
        return call_user_func_array($this->ns .'\\'.$this->func, func_get_args());
    }


    public function getCallsForMethod($func)
    {
        $calls = Registry::getFuncCallsFor($this->ns . '\\' . $func);
        return $calls;
    }
}