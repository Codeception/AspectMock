<?php
namespace demo;
use \AspectMock\Core\Registry as double;
use AspectMock\Proxy\ClassProxy;
use AspectMock\Proxy\InstanceProxy;

class VerifierTest extends \PHPUnit_Framework_TestCase
{
    use \Codeception\Specify;

    protected function tearDown()
    {
        double::clean();
    }

    // tests
    public function testVerifyInvocationClosures()
    {

        $info = array(
            'address' => 'foo',
            'email' => 'foo@bar.cl',
        );

        $user = new UserModel();
        double::registerObject($user);
        $user = new InstanceProxy($user);
        $user->setInfo($info);
        $user->setInfo([]);

        $matcher = function($params) use ($info) {
            $args = $params[0][0]; // first call, first arg
            $empty = $params[1][0]; // second call, first arg

            verify($info)->equals($args);
            verify($empty)->isEmpty();
        };

        $this->specify('closure was called', function() use ($user, $info, $matcher) {
            $user->verifyInvokedMultipleTimes('setInfo', 2, $matcher);
            $user->verifyInvoked('setInfo', $matcher);
        });
    }

    public function testVerifyMagicMethods()
    {
        $this->specify('works for class proxy', function() {
            // Set up user object.
            double::registerClass("demo\UserModel",
                                  ['renameUser'=>"Bob Jones", 'save'=>null]);
            $userProxy = new ClassProxy("demo\UserModel");
            $user = new UserModel(['name'=>"John Smith"]);

            // Rename the user via magic method.
            UserService::renameStatic($user, "Bob Jones");

            // Assert rename was counted.
            $userProxy->verifyInvoked('renameUser');
        });

        $this->specify('works for instance proxy', function() {
            // Set up user object.
            $user = new UserModel(['name'=>"John Smith"]);
            double::registerObject($user);
            $user = new InstanceProxy($user);

            // Rename the user via magic method.
            $user->renameUser("Bob Jones");

            // Assert rename was counted.
            $user->verifyInvoked('renameUser');
        });
    }
}
