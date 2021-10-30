<?php

declare(strict_types=1);

namespace demo;

use AspectMock\Core\Registry as double;
use AspectMock\Proxy\AnythingClassProxy;
use AspectMock\Proxy\ClassProxy;
use AspectMock\Proxy\InstanceProxy;
use Codeception\PHPUnit\TestCase;

final class MockFailedTest extends TestCase
{
    protected function _setUp()
    {
        $this->expectException('PHPUnit\Framework\ExpectationFailedException');
    }

    protected function _tearDown()
    {
        double::clean();
    }

    protected function user(): InstanceProxy
    {
        $user = new UserModel();
        double::registerObject($user);
        return new InstanceProxy($user);
    }

    protected function userProxy(): ClassProxy
    {
        $userProxy = new ClassProxy('demo\UserModel');
        double::registerClass('demo\UserModel');
        return $userProxy;
    }

    public function testInstanceInvoked()
    {
        $this->user()->verifyInvoked('setName');
    }

    public function testInstanceInvokedWithoutParams()
    {
        $user = $this->user();
        $user->setName('davert');
        $user->verifyInvoked('setName',[]);
    }

    public function testInstanceInvokedMultipleTimes()
    {
        $user = $this->user();
        $user->setName('davert');
        $user->setName('jon');
        $user->verifyInvokedMultipleTimes('setName',3);
    }

    public function testInstanceInvokedMultipleTimesWithoutParams()
    {
        $user = $this->user();
        $user->setName('davert');
        $user->setName('jon');
        $user->verifyInvokedMultipleTimes('setName',2,['davert']);
    }

    public function testClassMethodFails()
    {
        $userProxy = $this->userProxy();
        UserModel::tableName();
        UserModel::tableName();
        $userProxy->verifyInvokedOnce('tableName');
    }

    public function testClassMethodNeverInvokedFails()
    {
        $user = new UserModel();
        $userProxy = $this->userProxy();
        $user->setName('davert');
        $userProxy->verifyNeverInvoked('setName');

    }

    public function testClassMethodInvokedMultipleTimes()
    {
        $user = new UserModel();
        $userProxy = $this->userProxy();
        $user->setName('davert');
        $user->setName('bob');
        $userProxy->verifyInvokedMultipleTimes('setName',2,['davert']);
    }

    public function testClassMethodInvoked()
    {
        $user = new UserModel();
        $userProxy = $this->userProxy();
        $user->setName(1111);
        $userProxy->verifyInvoked('setName',[2222]);
    }

    public function testAnythingFail()
    {
        $anyProxy = new AnythingClassProxy('demo\UserModel');
        $any = $anyProxy->construct();
        $any->hello();
        $anyProxy->verifyInvoked('hello');
    }
}
