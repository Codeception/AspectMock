<?php

declare(strict_types=1);

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
 * ```
 */
class FuncProxy
{
    protected string $func;

    protected string $ns;
    
    protected string $fullFuncName;

    protected FuncVerifier $funcVerifier;

    public function __construct(string $namespace, string $func)
    {
        $this->func = $func;
        $this->ns = $namespace;
        $this->fullFuncName = $namespace . '/' . $func;
        $this->funcVerifier = new FuncVerifier($namespace);
    }

    public function verifyInvoked(array $params = null): void
    {
        $this->funcVerifier->verifyInvoked($this->func, $params);
    }

    public function verifyInvokedOnce(array $params = null): void
    {
        $this->funcVerifier->verifyInvokedMultipleTimes($this->func, 1, $params);
    }

    public function verifyNeverInvoked(array $params = null): void
    {
        $this->funcVerifier->verifyNeverInvoked($this->func, $params);
    }

    public function verifyInvokedMultipleTimes(int $times, array $params = null): void
    {
        $this->funcVerifier->verifyInvokedMultipleTimes($this->func, $times, $params);
    }

    /**
     * Executes mocked function with provided parameters.
     *
     * @return mixed
     */
    public function __invoke()
    {
        return call_user_func_array($this->ns .'\\'.$this->func, func_get_args());
    }

    public function getCallsForMethod(string $func): array
    {
        return Registry::getFuncCallsFor($this->ns . '\\' . $func);
    }
}
