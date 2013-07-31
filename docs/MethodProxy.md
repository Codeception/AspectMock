# AspectMock\Proxy\MethodProxy

Is created by a call to `verifyInvoked*` method of ClassProxy or InstanceProxy.
Used to check the results of method invocation.

``` php
<?php
$userClass = test::double('User');
$table = User::getTableName();
$userClass->verifyInvoked('getTableName')->returned($table);
?>
```

Class MethodProxy
 * package AspectMock\Proxy

## ->neverReturned


Verifies that call to a method never returned a value provided.

``` php
<?php
$user->name = 'davert';
$user->getName();
$user->verifyInvoked('getName')->neverReturned('jon');
?>
```

 * param $result


## ->result


Returns the result of last method invocation.

 * return mixed


## ->results


Returns an array with all the result of all method invocations.

 * return array


## ->returned


Verifies that method that was invoked returned specified result at least once.

``` php
<?php
$user->name = 'davert';
$user->getName();
$user->name = 'jon';
$user->getName();
$user->verifyInvoked('getName')->returned('davert');
?>
```

 * param $result


## ->returnedMultipleTimes


Checks that invoked method returned provided value exactly $times.

``` php
<?php
$user->name = 'davert';
$user->getName();
$user->name = 'jon';
$user->getName();
$user->getName();
$user->verifyInvoked('getName')->returnedOnce('davert');
$user->verifyInvoked('getName')->returnedMultipleTimes('jon', 2);
?>
```

 * param $result
 * param $times


## ->returnedOnce


Verifies that invoked method returned the specified result only once.

 * see returnedMultipleTimes
 * param $result
