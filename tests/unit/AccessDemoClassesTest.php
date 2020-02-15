<?php
namespace demo;

use PHPUnit\Framework\AssertionFailedError;

class AccessDemoClassesTest extends \PHPUnit\Framework\TestCase
{
    public function testUserModel()
    {
        $user = new UserModel(['name' => 'davert']);
        $this->assertEquals('davert', $user->getName());
    }

    public function testUserService()
    {
        $this->expectException(AssertionFailedError::class);
        $service = new UserService();
        $service->create(['name' => 'davert']);
    }

}