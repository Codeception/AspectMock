<?php

declare(strict_types=1);

namespace demo;

use AspectMock\Core\Registry as double;
use Codeception\PHPUnit\TestCase;

final class StubTest extends TestCase
{
    protected function _tearDown()
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
        $this->assertSame('saved!', $saved);
    }

    public function testCallback()
    {
        double::registerClass('\demo\UserModel', ['save' => function () { return $this->name; }]);
        $user = new UserModel(['name' => 'davert']);
        $name = $user->save();
        $this->assertSame('davert', $name);

    }

    public function testBindSelfCallback()
    {
        double::registerClass('\demo\UserModel', ['getTopSecret' => function () {
            return UserModel::$topSecret;
        }]);
        $topSecret = UserModel::getTopSecret();
        $this->assertSame('awesome', $topSecret);
    }

    public function testObjectInstance()
    {
        $user = new UserModel(['name' => 'davert']);
        double::registerObject($user,['save' => null]);
        $user->save();
    }

    public function testStaticAccess()
    {
        $this->assertSame('users', UserModel::tableName());
        double::registerClass('\demo\UserModel', ['tableName' => 'my_users']);
        $this->assertSame('my_users', UserModel::tableName());
    }

    public function testInheritance()
    {
        double::registerClass('\demo\UserModel', ['save' => false]);
        $admin = new AdminUserModel();
        $admin->save();
        $this->assertSame('Admin_111', $admin->getName());
    }

    public function testMagic()
    {
        double::registerClass('\demo\UserService', ['rename' => 'David Copperfield']);
        $admin = new UserService();
        $this->assertSame('David Copperfield', $admin->rename());

    }

    public function testMagicOfInheritedClass()
    {
        double::registerClass('\demo\AdminUserModel', ['renameUser' => 'David Copperfield']);
        $admin = new AdminUserModel();
        $this->assertSame('David Copperfield', $admin->renameUser());
    }

    public function testMagicStaticInherited()
    {
        double::registerClass('\demo\AdminUserModel', ['defaultRole' => 'admin']);
        $this->assertSame('admin', AdminUserModel::defaultRole());
    }

    public function testMagicStatic()
    {
        double::registerClass('\demo\UserModel', ['defaultRole' => 'admin']);
        $this->assertSame('admin', UserModel::defaultRole());
    }

//    public function testStubFunctionCall()
//    {
//        double::registerFunc('file_put_contents', 'Done');
//        $user = new UserModel();
//        $user->setName('David Bovie');
//        $this->assertSame('Done', $user->dump());
//    }
}
