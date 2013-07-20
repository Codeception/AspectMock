<?php
namespace demo;

class AccessDemoClassesTest extends \PHPUnit_Framework_TestCase
{
    public function testUserModel()
    {
        $user = new UserModel(['name' => 'davert']);
        $this->assertEquals('davert', $user->getName());
    }

    /**
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testUserService()
    {
        $service = new UserService();
        $service->create(['name' => 'davert']);
    }

}