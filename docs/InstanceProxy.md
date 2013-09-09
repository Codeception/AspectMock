# AspectMock\Proxy\InstanceProxy

InstanceProxy is a proxy for underlying object, mocked with test::double.
A real object can be returned with `getObject` methods.

``` php
<?php
$user1 = new User;
$user2 = test::double($user1);
$user1 instanceof User; // true
$user2 instanceof AspectMock\Proxy\InstanceProxy; // true

$user1 === $user2->getObject(); // true

?>
```

Contains verification methods and `class` property that points to `ClassProxy`.

``` php
<?php
$user = new User(['name' => 'davert']);
$user = test::double(new User);
// now $user is a proxy class of user
$this->assertEquals('davert', $user->getName()); // success
$user->verifyInvoked('getName'); // success
$this->assertInstanceOf('User', $user); // fail
?>
```

A `class` property allows to verify method calls to any instance of this class.
Constains a **ClassVerifier** object.

``` php
<?php
$user = test::double(new User);
$user->class->hasMethod('save');
$user->setName('davert');
$user->class->verifyInvoked('setName');
?>
```

Class InstanceVerifier
 * package AspectMock\Core
 * property $class ClassProxy

## ->getObject


Returns a real object that is proxified.

 * return mixed


## ->verifyInvoked


Verifies a method was invoked at least once.
In second argument you can specify with which params method expected to be invoked;

``` php
<?php
$user->verifyInvoked('save');
$user->verifyInvoked('setName',['davert']);

?>
```

 * param $name
 * param null $params
 * throws \PHPUnit_Framework_ExpectationFailedException
 * param array $params
 * throws fail


## ->verifyInvokedMultipleTimes


Verifies that method was called exactly $times times.

``` php
<?php
$user->verifyInvokedMultipleTimes('save',2);
$user->verifyInvokedMultipleTimes('dispatchEvent',3,['before_validate']);
$user->verifyInvokedMultipleTimes('dispatchEvent',4,['after_save']);
?>
```

 * param $name
 * param $times
 * param array $params
 * throws \PHPUnit_Framework_ExpectationFailedException


## ->verifyInvokedOnce


Verifies that method was invoked only once.

 * param $name
 * param array $params


## ->verifyNeverInvoked


Verifies that method was not called.
In second argument with which arguments is not expected to be called.

``` php
<?php
$user->setName('davert');
$user->verifyNeverInvoked('setName'); // fail
$user->verifyNeverInvoked('setName',['davert']); // fail
$user->verifyNeverInvoked('setName',['bob']); // success
$user->verifyNeverInvoked('setName',[]); // success
?>
```

 * param $name
 * param null $params
 * throws \PHPUnit_Framework_ExpectationFailedException
