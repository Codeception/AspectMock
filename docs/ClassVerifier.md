# AspectMock\Core\ClassVerifier

Verifies that class methods were called.
In the same manner can be used for static and instance calls:

``` php
<?php
$userModel = test::double('UserModel');
UserModel::tableName();
$user = new UserModel;
$user->save();
$userModel->verifyInvoked('tableName');
$userModel->verifyInvoked('save');

?>
```

## ->hasMethod


Asserts that class has method

``` php
<?php
$class = test::double('Model\User');
$class->hasMethod('save');
?>
```

 * param $method


## ->hasTrait



``` php
<?php
$class = test::double('Model\User');
$class->hasTrait('Model\Behavior\TimeStamp');
?>
```

 * param $trait


## ->isImplementing


Asserts that class implements interface.

``` php
<?php
$class = test::double('Model\User');
$class->isImplementing('Serializable');
?>
```

 * param $interface
 * return void


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
 * param array $params
 * throws fail
 * return mixed


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
 * return mixed


## ->verifyInvokedOnce


Verifies that method was invoked only once.

 * param $name
 * param array $params
 * return mixed


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
 * param array $params
 * throws \PHPUnit_Framework_ExpectationFailedException
 * return mixed
