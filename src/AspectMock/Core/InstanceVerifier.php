<?php
namespace AspectMock\Core;

/**
 * Acts as **proxy class** for contained object.
 * Contains verification methods and `class` property that points to class verificator.
 *
 * ``` php
 * <?php
 * $user = new User(['name' => 'davert']);
 * $user = test::double(new User);
 * // now $user is a proxy class of user
 * $this->assertEquals('davert', $user->getName()); // success
 * $user->verifyInvoked('getName'); // success
 * $this->assertInstanceOf('User', $user); // fail
 * ?>
 * ```
 *
 * A `class` property allows to verify method calls to any instance of this class.
 * Constains a **ClassVerifier** object.
 *
 * ``` php
 * <?php
 * $user = test::double(new User);
 * $user->class->hasMethod('save');
 * $user->setName('davert');
 * $user->class->verifyInvoked('setName');
 * ?>
 * ```
 *
 * Class InstanceVerifier
 * @package AspectMock\Core
 */

class InstanceVerifier extends Verifier {

    protected $instance;

    public $class;

    public function __construct($object)
    {
        $this->instance = $object;
        $this->className = get_class($object);
        $this->class = new ClassVerifier(get_class($object));
    }

    protected function callSyntax()
    {
        return "->";
    }
    
    protected function getCallsForMethod($method)
    {
        $calls = Registry::getInstanceCallsFor($this->instance);
        return isset($calls[$method])
            ? $calls[$method]
            : [];
    }
    

    // proxify calls to the methods
    public function __call($method, $args)
    {
        if (method_exists($this->instance, $method)) {
            return call_user_func_array([$this->instance, $method], $args);
        }
    }

    public function __get($property)
    {
        return $this->instance->$property;
    }

    public function __set($property, $value)
    {
        $this->instance->$property = $value;
    }
    
}