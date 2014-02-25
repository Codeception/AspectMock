<?php
namespace demo;
use AspectMock\Proxy\ClassProxy;
use AspectMock\Test as test;
use Codeception\Specify;

class ClassProxyTest extends \Codeception\TestCase\Test {

    use Specify;

    public function testSimpleClassValidations()
    {
        $class = test::double('demo\UserModel');
        /** @var $class ClassProxy **/
        verify($class->isDefined())->true();
        verify($class->hasMethod('setName'))->true();
        verify($class->hasMethod('setNothing'))->false();
        verify($class->hasProperty('name'))->true();
        verify($class->hasProperty('otherName'))->false();
        verify($class->traits())->isEmpty();
        verify($class->interfaces())->isEmpty();
        verify($class->parent())->null();
    }

    public function testMegaClassValidations()
    {
        $class = test::double('demo\MegaClass');
        /** @var $class ClassProxy **/
        verify($class->isDefined())->true();
        verify($class->hasMethod('setName'))->false();
        verify($class->traits())->contains('Codeception\Specify');
        verify($class->interfaces())->contains('Iterator');
        verify($class->parent())->equals('stdClass');
    }

    public function testUndefinedClass()
    {
        $this->setExpectedException('Exception');
        test::double('MyUndefinedClass');
    }

    public function testInstanceCreation()
    {
        $this->class = test::double('demo\UserModel');

        $this->specify('instance can be created from a class proxy', function() {
            $user = $this->class->construct(['name' => 'davert']);
            verify($user->getName())->equals('davert');
            $this->assertInstanceOf('demo\UserModel', $user);
        });

        $this->specify('instance can be created without constructor', function() {
            $user = $this->class->make();
            $this->assertInstanceOf('demo\UserModel', $user);
        });
    }

    public function testClassWithTraits() {
        // if a trait is used by more than one doubled class, when BeforeMockTransformer
        // runs on the second class it will see the trait's methods as being a part of
        // the class itself, and try to inject its code into the class, rather than the
        // trait. in failure mode, this test will result in:
        // Parse error: syntax error, unexpected 'if' (T_IF), expecting function (T_FUNCTION)
        // in [...]/TraitedModel2.php

        $unused = test::double('demo\TraitedClass1'); // this model uses `TraitedModelTrait`
        $class = test::double('demo\TraitedClass2');  // so does this one
        /** @var $class ClassProxy **/
        verify($class->isDefined())->true();
        verify($class->hasMethod('method1InTrait'))->true();
        verify($class->hasMethod('methodInClass'))->true();
    }

}
