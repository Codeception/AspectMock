<?php

declare(strict_types=1);

namespace AspectMock\Proxy;

use AspectMock\Core\Registry;
use Exception;
use ReflectionClass;

/**
 * ClassProxy represents a class of your project.
 *
 * * It can be used to verify methods invocations of a class.
 * * It provides some nice functions to construct class instances, with or without a constructor.
 * * It can be used to check class definitions.
 *
 *
 * ```php
 * <?php
 * $userModel = test::double('UserModel');
 * UserModel::tableName();
 * $user = $userModel->construct();
 * $user->save();
 * $userModel->verifyInvoked('tableName');
 * $userModel->verifyInvoked('save');
 * ```
 *
 * You can get a class name of a proxy via `className` property.
 *
 * ```php
 * <?php
 * $userModel = test::double('UserModel');
 * $userModel->className; // UserModel
 * ```
 *
 * Also, you can get the list of calls for a specific method.
 *
 * ```php
 * <?php
 * $user = test::double('UserModel');
 * $user->someMethod('arg1', 'arg2');
 * $user->getCallsForMethod('someMethod') // [ ['arg1', 'arg2'] ]
 * ```
 */
class ClassProxy extends Verifier
{
    protected ReflectionClass $reflected;

    public function __construct($class_name)
    {
        $this->className = $class_name;
        $this->reflected = new ReflectionClass($class_name);
    }

    public function getCallsForMethod($method)
    {
        $calls = Registry::getClassCallsFor($this->className);
        return $calls[$method] ?? [];
    }

    /**
     * Returns true if class exists.
     * Returns false if class is not defined yet, and was declared via `test::spec`.
     */
    public function isDefined(): bool
    {
       return true;
    }

    /**
     * Returns an array with all interface names of a class
     */
    public function interfaces(): array
    {
        return $this->getRealClass()->getInterfaceNames();
    }

    /**
     * Returns a name of the parent of a class.
     *
     * @return null
     */
    public function parent()
    {
        $parent = $this->getRealClass()->getParentClass();
        if ($parent) return $parent->name;
        
        return null;
    }

    /**
     * @param mixed $method
     */
    public function hasMethod($method): bool
    {
        return $this->getRealClass()->hasMethod($method);
    }

    /**
     * @param mixed $property
     */
    public function hasProperty($property): bool
    {
        return $this->getRealClass()->hasProperty($property);
    }

    /**
     * Returns array of all trait names of a class.
     */
    public function traits(): array
    {
        return $this->getRealClass()->getTraitNames();
    }

    private function getRealClass()
    {
        if (in_array('Go\Aop\Proxy', $this->reflected->getInterfaceNames())) {
            return $this->reflected->getParentClass();
        }
        
        return $this->reflected;
    }

    /**
     * Creates an instance of a class via constructor.
     *
     * ```php
     * <?
     * $user = test::double('User')->construct([
     *      'name' => 'davert',
     *      'email' => 'davert@mail.ua'
     * ]);
     * ```
     */
    public function construct(): object
    {
        return $this->reflected->newInstanceArgs(func_get_args());
    }

    /**
     * Creates a class instance without calling a constructor.
     *
     * ```php
     * <?
     * $user = test::double('User')->make();
     * ```
     */
    public function make(): object
    {
        return $this->reflected->newInstanceWithoutConstructor();
    }

    public function __call($method, $args)
    {
        throw new Exception("Called {$this->className}->{$method}, but this is a proxy for a class definition.\nProbably you were trying to access an instance method.\nConstruct an instance from this class");
    }
}
