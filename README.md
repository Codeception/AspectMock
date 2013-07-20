AspectMock
==========

AspectMock is not just an ordinary mocking framework you might ever seen in PHP.
With the power of Aspect Oriented programming and awesome [Go-AOP](https://github.com/lisachenko/go-aop-php) library,
AspectMock allows you to stub and mock practically anything in your PHP code!

## Code Pitch

### Stub and Mock Static Methods.

``` php
<?php
function testTableName()
{
	$this->assertEquals('users', UserModel::tableName());	
	$userModel = test::double('UserModel', ['tableName' => 'my_users']);
	$this->assertEquals('my_users', UserModel::tableName());
	$userModel->verifyInvoked('tableName');	
}
?>
```

### Stub and Mock Methods of a Class.

``` php
<?php
class UserService {
    function createUserByName($name)
    	$user = new User;
    	$user->setName($name);
    	return $user->save();
	}
}
?>
```

Method `$user->save` should not be executed, for not to access database.
Instead we will check it was actually called in this unit.

``` php
<?php
function testUserCreate()
{
	$user = test::double('User', ['save' => null]));
	$service = new UserService;
	$service->createUserByName('davert');
	$this->assertEquals('davert', $user->getName());
	$user->verifyInvoked('save');
}
?>
```

The method `$user->save` was called, but was replaced with a dummy. Thus, nothing was inserted to database.

### Really simple syntax

Only 4 methods for method call verification and one method to define stubs.

```
<?php
function testSimpleStubAndMock()
{	
	$user = test::double(new User, ['getName' => 'davert']);
	$this->assertEquals('davert', $user->getName());
	$user->verifyMethodInvoked('getName');
	$user->verifyMethodInvokedOnce('getName');
	$user->verifyMethodNeverInvoked('setName');
	$user->verifyMethodInvokedMultipleTimes('setName',1);
}
?>
```

To check that method `setName` was called with `davert` as argument.

``` php
<?php
$user->verifyMethodInvoked('setName', ['davert']);
?>
```

