<?php
namespace demo;
use \AspectMock\Core\Registry as double;

class StubTest extends \PHPUnit_Framework_TestCase
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

    public function testInheritance()
    {
        double::registerClass('\demo\UserModel', ['save' => false]);
        $admin = new AdminUserModel();
        $admin->save();
        $this->assertEquals('Admin_111', $admin->getName());
    }

    public function testMagic()
    {
        double::registerClass('\demo\UserService', ['rename' => 'David Copperfield']);
        $admin = new UserService();
        $this->assertEquals('David Copperfield', $admin->rename());

    }

    public function testMagicOfInheritedClass()
    {
        double::registerClass('\demo\AdminUserModel', ['renameUser' => 'David Copperfield']);
        $admin = new AdminUserModel();
        $this->assertEquals('David Copperfield', $admin->renameUser());
    }

    public function testMagicStaticInherited()
    {
        double::registerClass('\demo\AdminUserModel', ['defaultRole' => 'admin']);
        $this->assertEquals('admin', AdminUserModel::defaultRole());
    }

    public function testMagicStatic()
    {
        double::registerClass('\demo\UserModel', ['defaultRole' => 'admin']);
        $this->assertEquals('admin', UserModel::defaultRole());
    }

//    public function testStubFunctionCall()
//    {
//        double::registerFunc('file_put_contents', 'Done');
//        $user = new UserModel();
//        $user->setName('David Bovie');
//        $this->assertEquals('Done', $user->dump());
//    }

}