<?php

declare(strict_types=1);

namespace demo;

use AspectMock\Intercept\FunctionInjector;
use AspectMock\Test as test;
use PHPUnit\Framework\ExpectationFailedException;

final class FunctionInjectorTest extends \Codeception\TestCase\Test
{
    protected FunctionInjector $funcInjector;

    protected FunctionInjector $funcOptionalParameterInjector;

    protected FunctionInjector $funcReferencedParameterInjector;

    public function _before()
    {
        $this->funcInjector = new FunctionInjector('demo', 'strlen');
        $this->funcOptionalParameterInjector = new FunctionInjector('demo', 'explode');
        $this->funcReferencedParameterInjector = new FunctionInjector('demo', 'preg_match');
        test::clean();
    }

    public function testTemplate()
    {
        $php = $this->funcInjector->getPHP();
        verify($php)->stringContainsString("function strlen()");
        verify($php)->stringContainsString("return call_user_func_array('strlen', func_get_args());");
    }

    public function testReferencedParameterTemplate()
    {
        $php = $this->funcReferencedParameterInjector->getPHP();
        verify($php)->stringContainsString("function preg_match(\$p0, \$p1, &\$p2=NULL, \$p3=NULL, \$p4=NULL)");
        verify($php)->stringContainsString("case 5: \$args = [\$p0, \$p1, &\$p2, \$p3, \$p4]; break;");
        verify($php)->stringContainsString("case 4: \$args = [\$p0, \$p1, &\$p2, \$p3]; break;");
        verify($php)->stringContainsString("case 3: \$args = [\$p0, \$p1, &\$p2]; break;");
        verify($php)->stringContainsString("case 2: \$args = [\$p0, \$p1]; break;");
        verify($php)->stringContainsString("case 1: \$args = [\$p0]; break;");
        verify($php)->stringContainsString("return call_user_func_array('preg_match', \$args);");
    }

    public function testSave()
    {
        $this->funcInjector->save();
        exec('php -l '.$this->funcInjector->getFileName(), $output, $code);
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

    public function testFuncReturnsNull()
    {
        test::func('demo', 'strlen', null);
        verify(strlen('hello'))->equals(null);
    }

    public function testVerifier()
    {
        $func = test::func('demo', 'strlen', 10);
        verify(strlen('hello'))->equals(10);
        $func->verifyInvoked();
        $func->verifyInvoked(['hello']);
        $func->verifyInvokedOnce();
        $func->verifyInvokedOnce(['hello']);
        $func->verifyInvokedMultipleTimes(1, ['hello']);
        $func->verifyNeverInvoked(['hee']);
    }

    public function testVerifierFullyQualifiedNamespace()
    {
        $func = test::func('\demo', 'strlen', 10);
        verify(strlen('hello'))->equals(10);
        $func->verifyInvoked();
        $func->verifyInvoked(['hello']);
        $func->verifyInvokedOnce();
        $func->verifyInvokedOnce(['hello']);
        $func->verifyInvokedMultipleTimes(1, ['hello']);
        $func->verifyNeverInvoked(['hee']);
    }

    /**
     * @test
     */
    public function testFailedVerification()
    {
        $this->expectException(ExpectationFailedException::class);
        $func = test::func('demo', 'strlen', function() { return 10; });
        verify(strlen('hello'))->equals(10);
        $func->verifyNeverInvoked();
    }

    public function testReferencedParameter()
    {
        $func = test::func('\demo', 'preg_match', 10);
        verify(preg_match('@[0-9]+@', '1234', $match))->equals(10);
        test::clean();
        verify(preg_match('@[0-9]+@', '1234#', $match))->equals(1);
        verify($match[0])->equals('1234');
    }
}
