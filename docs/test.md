# AspectMock\test

`AspectMock\test` class is a builder of test doubles.
Any object can be enhanced and turned to test double with the call to `double` method.
This allows to redefine any method of object with your own, and adds mock verification methods.

If a class name is passed to `test::double`, instance of `ClassProxy` class is returned.
You can redefine class methods the same way, also mock verification methods are included as well.


## test::clean


Clears test doubles registry.
Should be called between tests.


## test::double


test::double registers class or object for to track its calls.
In second argument you may pass return values of its methods to redefine them.
Returns an object with call verification methods.

Example:

``` php
<?php

# simple
$user = test::double(new User, ['getName' => 'davert']);
$user->getName() // => davert
$user->verifyInvoked('getName'); // => success

# with closure
$user = test::double(new User, ['getName' => function() { return $this->login; }]);
$user->login = 'davert';
$user->getName(); // => davert

# on class
$ar = test::double('ActiveRecord', ['save' => null]);
$user = new User;
$user->name = 'davert';
$user->save(); // passes to ActiveRecord->save() and does not insert any SQL.
$ar->verifyInvoked('save'); // true

# on static method call

User::tableName(); // 'users'
$user = test::double('User', ['tableName' => 'fake_users']);
User::tableName(); // 'fake_users'
$user->verifyInvoked('tableName'); // success

?>
```

 * param $classOrObject
 * param array $params
 * return Core\ClassProxy|object


## test::dummy


Dummy is a class instance created without calling a constructor.
In a second argument you can redefine methods of that class instance.

``` php
<?php
$connection = test::dummy('MySQLConnection', ['connect' => null]);
?>
```

 * param $className
 * param array $params
 * return Core\ClassProxy|object


## test::fake


Fake has all methods replaced with dummies and created without calling a constructor.

``` php
<?php
test::fake('MySQLConnection');
?>
```

 * param $className
 * return Core\ClassProxy|object


## test::fakeExcept


As you may guess fakeExcepts is a fake where some methods are not replaced with dummies.

``` php
<?php
test::fakeExcept('MySQLConnection',['getConnectionName']);
?>
```

 * param $className
 * param array $exceptParams
 * return Core\ClassProxy|object
