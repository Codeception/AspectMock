<?php
namespace demo;
use AspectMock\Core\ClassProxy;
use \AspectMock\Core\Registry as double;

class MockTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        double::clean();
    }

    // tests
    public function testVerifyInstanceMethods()
    {
        $user = new UserModel();
        double::registerObject($user);
        $user->setName('davert');
        $user->verifyInvoked('setName');
        $user->verifyInvoked('setName',['davert']);
        $user->verifyInvokedMultipleTimes('setName',1);
        $user->verifyInvokedMultipleTimes('setName',1,['davert']);
        $user->verifyNeverInvoked('save');
        $user->verifyNeverInvoked('save',['params']);
        $user->verifyNeverInvoked('setName',['bugoga']);
    }

    public function testVerifyClassMethods()
    {
        double::registerClass('demo\UserModel',['save' => null]);
        $user = new ClassProxy('demo\UserModel');

        $service = new UserService();
        $service->create(array('name' => 'davert'));
        $user->verifyInvoked('save');
        $user->verifyInvoked('save',[]);
        $user->verifyInvokedMultipleTimes('save',1);
        $user->verifyNeverInvoked('getName');
    }

    public function testVerifyStaticMethods()
    {
        double::registerClass('demo\UserModel');
        $user = new ClassProxy('demo\UserModel');
        UserModel::tableName();
        $user->verifyInvoked('tableName');
    }

    public function testVerifyThatWasCalledWithParameters()
    {
        $user = new UserModel();
        double::registerObject($user);
        $user->setName('davert');
        $user->setName('jon');
        $user->verifyInvokedOnce('setName',['davert']);
    }

    public function testVerifyClassMethodCalled()
    {
        $user = new UserModel();
        $userProxy = new ClassProxy('demo\UserModel');
        double::registerClass('demo\UserModel');
        $user->setName('davert');
        $user->setName('jon');
        $userProxy->verifyInvokedMultipleTimes('setName',2);
        $userProxy->verifyInvokedOnce('setName',['jon']);
        $userProxy->verifyNeverInvoked('save');
        $userProxy->verifyNeverInvoked('setName',['bob']);

    }


}