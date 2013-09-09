<?php
use AspectMock\Test as test;

class NamespacesTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        test::clean();
    }

    public function testNamespaceGuess()
    {
        test::ns('demo');
        $this->assertFalse(test::spec('\\UserModel')->isDefined());
        $this->assertTrue(test::spec('UserModel')->isDefined());

    }

}