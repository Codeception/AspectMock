# AspectMock\Proxy\ClassProxy

ClassProxy represents a class of your project.

* It can be used to verify methods invocations of a class.
* It provides some nice functions to construct class instances, with or without a constructor.
* It can be used to check class definitions.


``` php
<?php
$userModel = test::double('UserModel');
UserModel::tableName();
$user = $userModel->construct();
$user->save();
$userModel->verifyInvoked('tableName');
$userModel->verifyInvoked('save');
?>
```

You can get a class name of a proxy via `className` property.

``` php
<?php
$userModel = test::double('UserModel');
$userModel->className; // UserModel
?>
```

## ->construct


Creates an instance of a class via constructor.

``` php
<?
$user = test::double('User')->construct([
     'name' => 'davert',
     'email' => 'davert@mail.ua'
]);

?>
```
 * return object


## ->hasMethod


 * param $method
 * return bool


## ->hasProperty


 * param $property
 * return bool


## ->interfaces


Returns an array with all interface names of a class

 * return array


## ->isDefined


Returns true if class exists.
Returns false if class is not defined yet, and was declared via `test::spec`.

 * return bool


## ->make


Creates a class instance without calling a constructor.

``` php
<?
$user = test::double('User')->make();

?>
```
 * return object


## ->parent


Returns a name of the parent of a class.

 * return null


## ->traits


Returns array of all trait names of a class.

 * return array


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
