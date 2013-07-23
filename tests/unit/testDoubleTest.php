<?php
use AspectMock\Test as test;

class testDoubleTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        test::clean();
    }

    public function testDoubleClass()
    {
        $user = test::double('demo\UserModel', ['save' => null]);
        (new demo\UserModel())->save();
        $user->verifyInvoked('save');
        \demo\UserModel::tableName();
        \demo\UserModel::tableName();
        $user->verifyInvokedMultipleTimes('tableName',2);
    }

    public function testDoubleObject()
    {
        $user = new demo\UserModel();
        $user = test::double($user, ['save' => null]);
        $user->save();
        $user->verifyInvoked('save');
        \demo\UserModel::tableName();
        $user->verifyNeverInvoked('tableName');
    }

    public function testFakeObject()
    {
        $user = test::fake('demo\UserModel');
        $user->setName('davert');
        $this->assertEquals(null, $user->getName());
        $user->save();
    }

    public function testDummyObject()
    {
        $user = test::dummy('demo\UserModel');
        $user->setName('davert');
        $this->assertEquals('davert', $user->getName());
    }

    public function testFakeExcept()
    {
        $user = test::fakeExcept('demo\UserModel', ['getName','setName']);
        $user->setName('davert');
        $this->assertEquals('davert', $user->getName());
        $user->save();
    }

}