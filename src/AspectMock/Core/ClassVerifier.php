<?php
namespace AspectMock\Core;

class ClassVerifier extends Verifier  {

    protected $reflected;

    public function __construct($class_name)
    {
        $this->className = $class_name;
        $this->reflected = new \ReflectionClass($class_name);

    }

    protected function getCallsForMethod($method)
    {
        $calls = Registry::getClassCallsFor($this->className);
        return isset($calls[$method])
            ? $calls[$method]
            : [];
    }

    /**
     * Asserts that class implements interface.
     *
     * ``` php
     * <?php
     * $class = test::double('Model\User');
     * $class->isImplementing('Serializable');
     * ?>
     * ```
     *
     * @param $interface
     * @return void
     */
    public function isImplementing($interface)
    {
        \PHPUnit_Framework_Assert::assertContains($interface, $this->reflected->getInterfaceNames());
    }

    /**
     * Asserts that class is extended from other.
     *
     * ``` php
     * <?php
     * $class = test::double('Model\User');
     * $class->isExtending('Model\Base');
     * ?>
     * ```
     * @param $class
     */
    protected function isExtending($class)
    {
        \PHPUnit_Framework_Assert::assertEquals($class, $this->reflected->getParentClass()->name);
    }

    /**
     * Asserts that class has method
     *
     * ``` php
     * <?php
     * $class = test::double('Model\User');
     * $class->hasMethod('save');
     * ?>
     * ```
     *
     * @param $method
     */
    public function hasMethod($method)
    {
        \PHPUnit_Framework_Assert::assertTrue($this->reflected->hasMethod($method));
    }

    /**
     * ``` php
     * <?php
     * $class = test::double('Model\User');
     * $class->hasProperty('is_saved');
     * ?>
     * ```
     *
     * @param $property
     */
    protected function hasProperty($property)
    {
        \PHPUnit_Framework_Assert::assertTrue($this->reflected->hasProperty($property));
    }

    /**
     *
     * ``` php
     * <?php
     * $class = test::double('Model\User');
     * $class->hasTrait('Model\Behavior\TimeStamp');
     * ?>
     * ```
     *
     * @param $trait
     */
    public function hasTrait($trait)
    {
        \PHPUnit_Framework_Assert::assertContains($trait, $this->reflected->getTraitNames());
    }

}