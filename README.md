AspectMock
==========

AspectMock is not an ordinary PHP mocking framework.
With the power of Aspect Oriented programming and the awesome [Go-AOP](https://github.com/lisachenko/go-aop-php) library,
AspectMock allows you to stub and mock practically anything in your PHP code!

**Documentation** | [Test Doubles Builder](https://github.com/Codeception/AspectMock/blob/master/docs/Test.md) | [ClassProxy](https://github.com/Codeception/AspectMock/blob/master/docs/ClassProxy.md) | [InstanceProxy](https://github.com/Codeception/AspectMock/blob/master/docs/InstanceProxy.md) | [MethodProxy](https://github.com/Codeception/AspectMock/blob/master/docs/MethodProxy.md)

**Stability**: alpha

[![Build Status](https://travis-ci.org/Codeception/AspectMock.png?branch=master)](https://travis-ci.org/Codeception/AspectMock)
[![Latest Stable Version](https://poser.pugx.org/codeception/aspect-mock/v/stable.png)](https://packagist.org/packages/codeception/aspect-mock)
[![Latest Unstable Version](https://poser.pugx.org/codeception/aspect-mock/v/unstable.png)](https://packagist.org/packages/codeception/aspect-mock)

## Motivation

PHP as a language that was not designed to be testable. Really. 
How would you fake the `time()` function to produce the same result for each test call?
Is there any way to stub a static method of a class? Can you redefine a class method at runtime?
Dynamic languages like Ruby or JavaScript allow us to do this. 
These features are essential for testing. AspectMock to the rescue!

Dozens of lines of untested code are written everyday in PHP.
In most cases, this code is not actually bad, 
but PHP does not provide capabilities to test it. You may suggest rewriting it from scratch following test driven design practices and use dependency injection wherever possible. Should this be done for stable working code? Well, there are much better ways to waste time.

With AspectMock you can unit-test practically any OOP code. PHP powered with AOP incorporates features of dynamic languages we have long been missing. There is no excuse for not testing your code.
You do not have to rewrite it from scratch to make it testable. Just install AspectMock with PHPUnit or Codeception and try to write some tests. It's really, really simple!


## Features

* Create test doubles for **static methods**.
* Create test doubles for **class methods called anywhere**.
* Redefine methods on the fly.
* Simple syntax that's easy to remember.

## Code Pitch

#### Allows stubbing and mocking of static methods.

Let's redefine static methods and verify their calls at runtime.

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

#### Allows replacement of class methods.

Testing code developed with the **ActiveRecord** pattern. Does the use of the ActiveRecord pattern sound like bad practice? No. But the code below is untestable in classic unit testing.

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

Without AspectMock you need to introduce `User` as an explicit dependency into class `UserService` to get it tested.
But lets leave the code as it is. It works. Nevertheless, we should still test it to avoid regressions.

We don't want the `$user->save` method to actually get executed, as it will hit the database.
Instead we will replace it with a dummy and verify that it gets called by `createUserByName`:

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

#### Beautifully simple

Only 4 methods are necessary for method call verification and one method to define test doubles:

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

## Wow! But how does it work?

No PECL extensions is required. The [Go! AOP](http://go.aopphp.com/) library does the heavy lifting by patching autoloaded PHP classes on the fly. By introducing pointcuts to every method call, Go! allows intercepting practically any call to a method. AspectMock is a very tiny framework consisting of only 8 files using the power of the [Go! AOP Framework](http://go.aopphp.com/). Check out Aspect Oriented Development and the Go! library itself.

## Requirements

PHP >= 5.4 + [Go! AOP Requirements](https://github.com/lisachenko/go-aop-php#requirements)

## Installation via Composer

##### 1. Add aspect-mock to your composer.json.

```
{
	"require-dev": {
		"codeception/aspect-mock": "*"
	}
}
```

##### 2. Install AspectMock with Go! AOP as a dependency.

```
php composer.phar update
```

##### 3. Configure AspectMock

Include `AspectMock\Kernel` class into your tests bootstrap file. 

##### With Composer's Autoloader

``` php
<?php
include __DIR__.'/../vendor/autoload.php'; // composer autoload

$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'includePaths' => [__DIR__.'/../src']
]);
?>
```

If your project uses Composer's autoloader, that's all you need to get started.

##### With Custom Autoloader

If you use a custom autoloader (like in Yii/Yii2 frameworks), you should explicitly point AspectMock to modify it:

``` php
<?php
include __DIR__.'/../vendor/autoload.php'; // composer autoload

$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'includePaths' => [__DIR__.'/../src']
]);
$kernel->loadFile('YourAutoloader.php'); // path to your autoloader
?>
```

Load all autoloaders of your project this way, if you do not rely on Composer entirely.

##### Without Autoloader

If it still doesn't work for you... 

Explicitly load all required files before testing:


``` php
<?php
include __DIR__.'/../vendor/autoload.php'; // composer autoload

$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'includePaths' => [__DIR__.'/../src']
]);
require 'YourAutoloader.php';
$kernel->loadPhpFiles('/../common');
?>
```

## Usage in PHPUnit

Use newly created `bootstrap` in your `phpunit.xml` configuration. Also disable `backupGlobals`:

``` xml
<phpunit bootstrap="bootstrap.php" backupGlobals="false">
```

Clear the test doubles registry between tests.

``` php
<?php
use AspectMock\Test as test;

class UserTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        test::clean(); // remove all registered test doubles
    }

    public function testDoubleClass()
    {
        $user = test::double('demo\UserModel', ['save' => null]);
        \demo\UserModel::tableName();
        \demo\UserModel::tableName();
        $user->verifyInvokedMultipleTimes('tableName',2);
    }

?>
```

## Usage in Codeception.

Include `AspectMock\Kernel` into `tests/_bootstrap.php`.
We recommend including a call to `test::clean()` from your `CodeHelper` class:

``` php
<?php
namespace Codeception\Module;

class CodeHelper extends \Codeception\Module
{
	function _after(\Codeception\TestCase $test)
	{
		\AspectMock\Test::clean();
	}
}
?>
```

## Improvements?

There is guaranteed to be room for improvements. This framework was not designed to do everything you might ever need (see notes below). But if you feel like you require a feature, please submit a Pull Request. It's pretty easy since there's not much code, and the Go! library is very well documented.

## Credits

Initial version developed in 1 day by **Michael Bodnarchuk**.

License: **MIT**.

Powered by [Go! Aspect-Oriented Framework](http://go.aopphp.com/)
