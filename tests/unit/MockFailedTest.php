<?php
namespace demo;

use AspectMock\Proxy\Anything;
use AspectMock\Proxy\AnythingClassProxy;
use AspectMock\Proxy\ClassProxy;
use AspectMock\Proxy\InstanceProxy;
use \AspectMock\Core\Registry as double;

class MockFailedTest extends \PHPUnit_Framework_TestCase 
{
    protected function setUp()
    {
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
    }        
    
    protected function tearDown()
    {
        double::clean();
    }

    protected function user()
    {
        $user = new UserModel();
        double::registerObject($user);
        $user = new InstanceProxy($user);
        return $user;
    }

    protected function userProxy()
    {
        $userProxy = new ClassProxy('demo\UserModel');
        double::registerClass('demo\UserModel');
        return $userProxy;
    }

    public function testInstanceInvoked()
    {
        $this->user()->verifyInvoked('setName');
    }

    public function testInstanceInvokedWothoutParams()
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