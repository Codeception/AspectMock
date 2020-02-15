<?php
use AspectMock\Test as test;
use Test\ns1\TestPhp7Class;

class testDoubleTest extends \Codeception\Test\Unit
{
    use Codeception\Specify;
    use demo\WorkingTrait;

    protected function _tearDown()
    {
        test::clean();
    }

    public function testDoubleClass()
    {
        $user = test::double('demo\UserModel', ['save' => null]);
        (new demo\UserModel())->save();
        $user->verifyInvoked('save');
        \demo\UserModel::tableName();
        \demo\UserModel::tableName();
        $user->verifyInvokedMultipleTimes('tableName',2);

        $this->specify('disabling all methods', function() use ($user) {
            test::methods($user, []);
            verify(\demo\UserModel::tableName())->null();
        });
    }

    public function testDoubleFullyQualifiedClass()
    {
        $user = test::double('\demo\UserModel', ['save' => null]);
        (new demo\UserModel())->save();
        $user->verifyInvoked('save');
        \demo\UserModel::tableName();
        \demo\UserModel::tableName();
        $user->verifyInvokedMultipleTimes('tableName',2);

        $this->specify('disabling all methods', function() use ($user) {
            test::methods($user, []);
            verify(\demo\UserModel::tableName())->null();
        });
    }

    public function testDoubleObject()
    {
        $user = new demo\UserModel();
        $user = test::double($user, ['save' => null]);
        $user->save();
        $user->verifyInvoked('save');

        $this->specify('only selected methods can be added to instance', function() use ($user) {
            $user = test::methods($user, ['setName']);
            $user->setName('davert');
            verify($user->getName())->notEquals('davert');
            verify($user->getName())->null();
            verify($user->getObject()->getName())->null();
        });


    }

    public function testSpecUndefinedClass()
    {
        $class = test::spec('MyVirtualClass');
        /** @var $class ClassProxy **/
        $this->assertFalse($class->isDefined());
        $this->assertFalse($class->hasMethod('__toString'));
        $this->assertFalse($class->hasMethod('edit'));
        verify($class->interfaces())->isEmpty();
        $this->any = $class->make();
        $this->any = $class->construct();

        $this->specify('should return original class name', function() {
            $this->assertStringContainsString('Undefined', (string)$this->any);
            $this->assertStringContainsString('MyVirtualClass', (string)$this->any->__toString());
        });

        $this->specify('any method can be invoked', function() {
           $this->assertInstanceOf('AspectMock\Proxy\Anything', $this->any->doSmth()->withTHis()->andThatsAll()->null());
        });

        $this->specify('any property can be accessed', function() {
            $this->any->that = 'xxx';
           $this->assertInstanceOf('AspectMock\Proxy\Anything', $this->any->this->that->another);
        });

        $this->specify('can be used as array', function() {
            $this->any['has keys'];
            unset($this->any['this']);
            $this->any['this'] = 'that';
            $this->assertFalse(isset($this->any['that']));
            $this->assertInstanceOf('AspectMock\Proxy\Anything', $this->any['keys']);
        });

        $this->specify('can be iterated', function() {
            foreach ($this->any as $anything) {}
        });

        $this->specify('proxifies magic method calls', function() {
            $any = test::double($this->any);
            $any->callMeMaybe();
            $any->name = 'hello world';
            $this->assertInstanceOf('AspectMock\Proxy\Anything', $any->name);
            verify($any->class->className)->equals('AspectMock\Proxy\Anything');
        });
    }

    public function testCleanupSpecificClasses()
    {
        $service = test::double('demo\UserService',['updateName' => 'hello'])->make();
        test::double('demo\UserModel',['tableName' => 'my_table']);
        verify(demo\UserModel::tableName())->equals('my_table');
        test::clean('demo\UserModel');
        verify(demo\UserModel::tableName())->equals('users');
        verify($service->updateName(new \demo\UserModel()))->equals('hello');
    }

    public function testCleanupSpecificObj()
    {
        $model = test::double('demo\UserModel');
        $user1 = test::double($model->make(), ['getName' => 'bad boy']);
        $user2 = test::double($model->make(), ['getName' => 'good boy']);
        verify($user1->getName())->equals('bad boy');
        verify($user2->getName())->equals('good boy');
        test::clean($user1);
        verify($user1->getName())->null();
        verify($user2->getName())->equals('good boy');
    }

    public function testPhp7Features()
    {
        if (PHP_MAJOR_VERSION < 7) {
            $this->markTestSkipped('PHP 7 only');
        }
        \AspectMock\Kernel::getInstance()->loadFile(codecept_data_dir() . 'php7.php');
        test::double(TestPhp7Class::class, [
            'stringSth' => true,
            'floatSth' => true,
            'boolSth' => true,
            'intSth' => true,
            'callableSth' => true,
            'arraySth' => true,
            'variadicStringSthByRef' => true,
            'stringRth' => 'hey',
            'floatRth' => 12.2,
            'boolRth' => true,
            'intRth' => 12,
            'callableRth' => function() { return function() {}; },
            'arrayRth' => [1],
            'exceptionRth' => new \Exception(),
        ]);
        $obj = new TestPhp7Class;
        $this->assertTrue($obj->stringSth('123'));
        $this->assertTrue($obj->floatSth(123));
        $this->assertTrue($obj->boolSth(false));
        $this->assertTrue($obj->intSth(12));
        $this->assertTrue($obj->callableSth(function() {}));
        $this->assertTrue($obj->arraySth([]));
        $str = 'hello';
        $this->assertTrue($obj->variadicStringSthByRef($str, $str));
        $this->assertEquals('hey', $obj->stringRth($str));
        $this->assertEquals(12.2, $obj->floatRth(12.12));
        $this->assertTrue($obj->boolRth(false));
        $this->assertEquals(12, $obj->intRth(15));
        //$this->assertInternalType('callable', $obj->callableRth(function() {}));
        $this->assertEquals([1], $obj->arrayRth([]));
        $this->assertInstanceOf('Exception', $obj->exceptionRth(new \Exception('ups')));
    }


}
