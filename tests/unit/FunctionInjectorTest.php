<?php
namespace demo;

use AspectMock\Intercept\FunctionInjector;
use AspectMock\Test as test;
use Codeception\TestCase;

class FunctionInjectorTest extends \Codeception\TestCase\Test
{
    /**
     * @var FunctionInjector
     */
    protected $funcInjector;

    public function _before()
    {
        $this->funcInjector = new FunctionInjector('demo', 'strlen');
        test::clean();
    }

    public function testTemplate()
    {
        $php = $this->funcInjector->getPHP();
        verify($php)->contains("function strlen()");
        verify($php)->contains("return call_user_func_array('strlen', func_get_args());");
    }

    public function testSave()
    {
        $this->funcInjector->save();
        exec('php -l '.$this->getFileName(), $output, $code);
        verify($code)->equals(0);
        codecept_debug($this->funcInjector->getPHP());
    }

    public function testLoadFunc()
    {
        $this->funcInjector->save();
        codecept_debug($this->funcInjector->getFileName());
        $this->funcInjector->inject();
        verify(strlen('hello'))->equals(5);
    }

    public function testReimplementFunc()
    {
        test::func('demo', 'strlen', 10);
        verify(strlen('hello'))->equals(10);
    }

    public function testVerifier()
    {
        $func = test::func('demo', 'strlen', 10);
        expect(strlen('hello'))->equals(10);
        $func->verifyInvoked();
        $func->verifyInvoked(['hello']);
        $func->verifyInvokedOnce();
        $func->verifyInvokedOnce(['hello']);
        $func->verifyInvokedMultipleTimes(1, ['hello']);
        $func->verifyNeverInvoked(['hee']);
    }

    public function testFailedVerification()
    {
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $func = test::func('demo', 'strlen', function() { return 10; });
        expect(strlen('hello'))->equals(10);
        $func->verifyNeverInvoked('strlen');
    }

}

