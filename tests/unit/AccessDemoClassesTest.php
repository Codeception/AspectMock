<?php

declare(strict_types=1);

namespace demo;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

final class AccessDemoClassesTest extends TestCase
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
