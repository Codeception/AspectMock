AspectMock
==========

AspectMock is not an ordinary mocking framework you might ever seen in PHP.
With the power of Aspect Oriented programming and awesome [Go-AOP](https://github.com/lisachenko/go-aop-php) library,
AspectMock allows you to stub and mock practically anything in your PHP code!

## Features

* create stubs and mocks for **static methods**.
* create stubs and mocks for **class methods called anywhere**.
* redefine methods in existing objects
* a simple syntax you don't need to remember.

## Code Pitch

#### 1. Allows to stub and mock static methods.

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

#### 2. Allows to stub and mock methods of a class.

``` php
<?php
class UserService {
    function createUserByName($name)
    	$user = new User;
    	$user->setName($name);
    	$user->save();
	}
}
?>
```

We dont want that method `$user->save` was actually executed, because it will hit the database.
Instead we will replace it with dummy, and check it was actually called on `createUserByName` call.

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

#### 3. Beautifuly simple

Only 4 methods for method call verification and one method to define stubs.

``` php
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

## Requirements

PHP >= 5.4 + [Go! AOP Requirements](https://github.com/lisachenko/go-aop-php#requirements)

## Installation via Composer

##### 1. Add `aspect-mock` to your `composer.yml`.

``` json
{
	'require-dev': {
		'codeception/aspect-mock': '*'
	}
}
```

##### 2. Install AspectMock with Go! AOP as a dependency.

```
php composer.phar update
```

#### 3. Configure AspectMock\Kernel in front controller of your application.

Include `AspectMock\Kernel` class into your bootstrap file. 
Details and examples on AspectKernel configuation you can find in [Go! AOP Documentation].

In your `index.php`:

``` php
<?php
include __DIR__.'/../vendor/autoload.php'; // composer autoload

$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'cacheDir' => __DIR__.'/../tests/cache',
    'includePaths' => [__DIR__.'/../src']
]);
?>
```

AspectMock\Kernel should be initialized after the autoloader was required.

Aspects configuration in:
* [Symfony](https://github.com/lisachenko/symfony-aspect)
* [Laravel](https://github.com/lisachenko/laravel-aspect)
* [Zend Framework 2](https://github.com/lisachenko/zf2-aspect)
* [Yii](https://github.com/lisachenko/yii-aspect)

* 

