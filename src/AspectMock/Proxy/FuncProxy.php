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
class FuncProxy
{
    /**
     * @var string
     */
    protected $func;
    
    /**
     * @var string
     */
    protected $ns;
    
    /**
     * @var string
     */
    protected $fullFuncName;

    /**
     * @var FuncVerifier
     */
    protected $funcVerifier;

    /**
     * @param string
     * @param string
     */
    public function __construct($namespace, $func)
    {
        $this->func = $func;
        $this->ns = $namespace;
        $this->fullFuncName = $namespace . '/' . $func;
        $this->funcVerifier = new FuncVerifier($namespace);
    }

    /**
     * @param null|array $params
     */
    public function verifyInvoked(array $params = null)
    {
        $this->funcVerifier->verifyInvoked($this->func, $params);
    }

    /**
     * @param null|array $params
     */
    public function verifyInvokedOnce(array $params = null)
    {
        $this->funcVerifier->verifyInvokedMultipleTimes($this->func, 1, $params);
    }

    /**
     * @param null|array $params
     */
    public function verifyNeverInvoked(array $params = null)
    {
        $this->funcVerifier->verifyNeverInvoked($this->func, $params);
    }

    /**
     * @param int        $times
     * @param null|array $params
     */
    public function verifyInvokedMultipleTimes($times, array $params = null)
    {
        $this->funcVerifier->verifyInvokedMultipleTimes($this->func, $times, $params);
    }

    /**
     * Executes mocked function with provided parameters.
     * @return mixed
     */
    public function __invoke()
    {
        return call_user_func_array($this->ns .'\\'.$this->func, func_get_args());
    }

    /**
     * @param string
     * @return array
     */
    public function getCallsForMethod($func)
    {
        $calls = Registry::getFuncCallsFor($this->ns . '\\' . $func);
        return $calls;
    }
}
