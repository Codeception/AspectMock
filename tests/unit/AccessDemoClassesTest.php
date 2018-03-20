<?php
namespace demo;

class AccessDemoClassesTest extends \PHPUnit\Framework\TestCase
{
    public function testUserModel()
    {
        $user = new UserModel(['name' => 'davert']);
        $this->assertEquals('davert', $user->getName());
    }

    /**
     * @expectedException \PHPUnit\Framework\AssertionFailedError
     */
    public function testUserService()
    {
        $service = new UserService();
        $service->create(['name' => 'davert']);
    }

}