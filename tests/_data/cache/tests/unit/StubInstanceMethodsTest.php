<?php
namespace demo;
use \AspectMock\Core\Registry as double;

class StubInstanceMethodsTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        double::clean();
    }
    // tests
    public function testSaveStub()
    {
        double::registerClass('\demo\UserModel', ['save' => null]);
        $user = new UserModel();
        $user->save();
    }

    public function testSaveAgain()
    {
        double::registerClass('\demo\UserModel', ['save' => "saved!"]);
        $user = new UserModel();
        $saved = $user->save();
        $this->assertEquals('saved!', $saved);
    }

    public function testCallback()
    {
        double::registerClass('\demo\UserModel', ['save' => function () { return $this->name; }]);
        $user = new UserModel(['name' => 'davert']);
        $name = $user->save();
        $this->assertEquals('davert', $name);

    }

    public function testObjectInstance()
    {
        $user = new UserModel(['name' => 'davert']);
        double::registerObject($user,['save' => null]);
        $user->save();
    }

    public function testStaticAccess()
    {
        $this->assertEquals('users', UserModel::tableName());
        double::registerClass('\demo\UserModel', ['tableName' => 'my_users']);
        $this->assertEquals('my_users', UserModel::tableName());
    }

}