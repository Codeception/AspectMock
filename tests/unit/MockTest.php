<?php
namespace demo;
use \AspectMock\Core\Registry as double;
use AspectMock\Proxy\ClassProxy;
use AspectMock\Proxy\InstanceProxy;
use Codeception\PHPUnit\TestCase;

class MockTest extends TestCase
{
    use \Codeception\Specify;

    protected function _tearDown()
    {
        double::clean();
    }

    // tests
    public function testVerifyInstanceMethods()
    {
        $user = new UserModel();
        double::registerObject($user);
        $user = new InstanceProxy($user);
        $user->setName('davert');

        $this->specify('setName() was invoked', function() use ($user) {
            $user->verifyInvoked('setName');
            $user->verifyInvoked('setName',['davert']);
            $user->verifyInvokedMultipleTimes('setName',1);
            $user->verifyInvokedMultipleTimes('setName',1,['davert']);
            $user->verifyNeverInvoked('setName',['bugoga']);
        });

        $this->specify('save() was not invoked', function() use ($user) {
            $user->verifyNeverInvoked('save');
            $user->verifyNeverInvoked('save',['params']);
        });

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
        $user = new InstanceProxy($user);
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
        verify($user->getName())->equals('jon');

    }

    /**
     * Allow verifying extended methods.
     *
     * Given:
     *
     * <code>
     * <?php
     * class A {
     *     public function super() {}
     * }
     *
     * class B extends A {}
     * ?>
     * </code>
     *
     * Verification:
     *
     * <code>
     * <?php
     * double::registerClass('A');
     * double::registerClass('B');
     *
     * $parentProxy = new ClassProxy('A');
     * $childProxy = new ClassProxy('B');
     *
     * $b = new B();
     * $b->super();
     *
     * // Will pass
     * $parentProxy->verifyInvoked('super');
     * $childProxy->verifyInvoked('super');
     * ?>
     * </code>
     */
    public function testVerifyClassInheritedMethodCalled()
    {
        $adminUser = new AdminUserModel();

        double::registerClass(\demo\UserModel::class);
        double::registerClass(\demo\AdminUserModel::class);

        $userProxy = new ClassProxy(\demo\UserModel::class);
        $adminUserProxy = new ClassProxy(\demo\AdminUserModel::class);

        $adminUser->getName();

        $userProxy->verifyInvokedOnce('getName');
        $adminUserProxy->verifyInvokedOnce('getName');
    }

}