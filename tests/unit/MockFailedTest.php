<?php
namespace demo;

use AspectMock\Core\ClassProxy;
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
        $this->user()
            ->setName('davert')
            ->verifyInvoked('setName',[]);
    }

    public function testInstanceInvokedMultipleTimes()
    {
        $this->user()
            ->setName('davert')
            ->setName('jon')
            ->verifyInvokedMultipleTimes('setName',3);
    }

    public function testInstanceInvokedMultipleTimesWithoutParams()
    {
        $this->user()
            ->setName('davert')
            ->setName('jon')
            ->verifyInvokedMultipleTimes('setName',2,['davert']);
    }

    public function testClassMethodFails()
    {
        double::registerClass('demo\UserModel');
        $user = new ClassProxy('demo\UserModel');
        UserModel::tableName();
        UserModel::tableName();
        $user->verifyInvokedOnce('tableName');
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
    
}