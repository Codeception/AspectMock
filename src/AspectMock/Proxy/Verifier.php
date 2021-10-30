<?php

declare(strict_types=1);

namespace AspectMock\Proxy;

use AspectMock\Util\ArgumentsFormatter;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * Interface `Verifiable` defines methods to verify method calls.
 * Implementation may differ for class methods and instance methods.
 */
abstract class Verifier {

    /**
     *  Name of a class.
     */
    public $className;

    protected string $invokedFail = "Expected %s to be invoked but it never occurred. Got: %s";

    protected string $notInvokedMultipleTimesFail = "Expected %s to be invoked %s times but it never occurred.";

    protected string $invokedMultipleTimesFail = "Expected %s to be invoked but called %s times but called %s.";

    protected string $neverInvoked = "Expected %s not to be invoked but it was.";

    abstract public function getCallsForMethod($method);

    protected function callSyntax($method): string
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
     * ```
     */
    public function verifyInvoked(string $name, array $params = null)
    {
        $calls = $this->getCallsForMethod($name);
        $separator = $this->callSyntax($name);

        if (empty($calls)) throw new ExpectationFailedException(sprintf($this->invokedFail, $this->className.$separator.$name, ''));

        if (is_array($params)) {
            foreach ($calls as $args) {
                if ($this->onlyExpectedArguments($params, $args) === $params) return;
            }

            $params    = ArgumentsFormatter::toString($params);
            $gotParams = ArgumentsFormatter::toString($calls[0]);
            throw new ExpectationFailedException(sprintf($this->invokedFail, $this->className.$separator.$name.sprintf('(%s)', $params), $this->className.$separator.$name.sprintf('(%s)', $gotParams)));
        } elseif (is_callable($params)) {
            $params($calls);
        }
    }

    /**
     * Verifies that method was invoked only once.
     */
    public function verifyInvokedOnce(string $name, array $params = null): void
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
     * ```
     *
     * @throws ExpectationFailedException
     */
    public function verifyInvokedMultipleTimes(string $name, int $times, array $params = null)
    {
        if ($times == 0) return $this->verifyNeverInvoked($name, $params);

        $calls = $this->getCallsForMethod($name);
        $separator = $this->callSyntax($name);

        if (empty($calls)) throw new ExpectationFailedException(sprintf($this->notInvokedMultipleTimesFail, $this->className.$separator.$name, $times));

        if (is_array($params)) {
            $equals = 0;
            foreach ($calls as $args) {
                if ($this->onlyExpectedArguments($params, $args) === $params) {
                    ++$equals;
                }
            }

            if ($equals == $times) {
                Assert::assertTrue(true);
                return;
            }

            $params = ArgumentsFormatter::toString($params);
            throw new ExpectationFailedException(sprintf($this->invokedMultipleTimesFail, $this->className.$separator.$name.sprintf('(%s)', $params), $times, $equals));
        } elseif (is_callable($params)) {
            $params($calls);
        }

        $num_calls = count($calls);
        if ($num_calls != $times) throw new ExpectationFailedException(sprintf($this->invokedMultipleTimesFail, $this->className.$separator.$name, $times, $num_calls));

        Assert::assertTrue(true);
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
     * ```
     *
     * @throws ExpectationFailedException
     */
    public function verifyNeverInvoked(string $name, array $params = null)
    {
        $calls = $this->getCallsForMethod($name);
        $separator = $this->callSyntax($name);

        if (is_array($params)) {
            if (empty($calls)) {
                Assert::assertTrue(true);
                return;
            }

            foreach ($calls as $args) {
                if ($this->onlyExpectedArguments($params, $args) === $params) {
                    throw new ExpectationFailedException(sprintf($this->neverInvoked, $this->className));
                }
            }

            Assert::assertTrue(true);
            return;
        }

        if (count($calls) > 0) {
            throw new ExpectationFailedException(sprintf($this->neverInvoked, $this->className.$separator.$name));
        }

        Assert::assertTrue(true);
    }
}
