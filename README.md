AspectMock
==========

AspectMock is not an ordinary mocking framework you might ever seen in PHP.
With the power of Aspect Oriented programming and awesome [Go-AOP](https://github.com/lisachenko/go-aop-php) library,
AspectMock allows you to stub and mock practically anything in your PHP code!

**Documentation** | [Test Doubles Builder](https://github.com/Codeception/AspectMock/blob/master/docs/Test.md) | [ClassProxy](https://github.com/Codeception/AspectMock/blob/master/docs/ClassProxy.md) | [InstanceProxy](https://github.com/Codeception/AspectMock/blob/master/docs/InstanceProxy.md) | [MethodProxy](https://github.com/Codeception/AspectMock/blob/master/docs/MethodProxy.md)

**Stability**: alpha

[![Build Status](https://travis-ci.org/Codeception/AspectMock.png?branch=master)](https://travis-ci.org/Codeception/AspectMock)
[![Latest Stable Version](https://poser.pugx.org/codeception/aspect-mock/v/stable.png)](https://packagist.org/packages/codeception/aspect-mock)
[![Latest Unstable Version](https://poser.pugx.org/codeception/aspect-mock/v/unstable.png)](https://packagist.org/packages/codeception/aspect-mock)

## Motivation

PHP as a language that was not designed to be testable. Really. 
How would you fake the `time()` function so it produced the same result for each test call?
Is there any way to stub a static method of a class? Can you redefine a class method in runtime?
Dynamic languages like Ruby or JavaScript allow us to do this. 
This features are essential for testing. And finally they are brought to PHP by AspectMock mocking framework.

Dozens lines of untested code are written everyday in PHP. In most cases, this code is not actually that bad, 
but PHP does not provide capabilities to get it tested. You may suggest to rewrite that code from scratch following test driven design practices and use dependency injection wherever it is possible. Should this be done for stable working code? Well, there are much more better ways to waste time.

With AspectMock you can unit-test practically any OOP code. PHP powered with AOP takes all the features of dynamic languages, we missed before. Thus, there is no excuse for not testing your code. You do not have to rewrite it from scratch to make it testable. Just install AspectMock with PHPUnit or Codeception. And try to write some tests. It's really really simple.


## Features

* create test doubles for **static methods**.
* create test doubles for **class methods called anywhere**.
* redefine methods on the fly
* simple syntax you will easily remember.

## Code Pitch

#### Allows to stub and mock static methods.

We are redefining static methods and verify their calls in runtime.

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

#### Allows to replace methods of a class.

Testing code developed with **ActiveRecord** pattern. Does usage of ActiveRecord pattern sounds like a bad practice? No. But the code below is untestable in classical unit testing.

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

Without AspectMock you need to introduce `User` as explicit dependency into class `UserService` to get it tested.
But lets leave the code as it is. It works. But lets test it to avoid regressions.

At first, we don't want method `$user->save` was actually executed, as it will hit the database.
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

#### Beautifully simple

Only 4 methods for method call verification and one method to define test doubles.

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

No PECL extensions is required. [Go! AOP](http://go.aopphp.com/) library does the awesome job with patching autoloaded PHP classes on the fly. By introducing pointcuts to every method call, Go! allows to intercept practically any call to a method. AspectMock is a very tiny framework with only 8 files within, that just uses this strong power of [Go! AOP Framework](http://go.aopphp.com/). We recommend to check out Aspect Oriented Development and the Go! library itself.

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

If your project uses Composer's autoloader, this is quite enough for the start.

##### With Custom Autoloader

If you use a custom autoloader (like in Yii/Yii2 frameworks), you should explicitly point AspectMock to modify autoloaders:

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

In this way you should load all autoloaders of your project if you do not rely on Composer entirely.

##### Without Autoloader

If it still didn't work for you... 

Explicitly load all required files before the test:


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

Clear test doubles registry between tests.

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
We recommend to include `test::clean()` call to your `CodeHelper` class.

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

Sure there is a room for improvements, this framework was not designed to do everything you might ever need (see notes below). But If you feel like you require a feature, please submit a Pull Request. This is pretty easy, there is not to much code, and Go! library is very well documented. 

## Credits

Initial version developed in 1 day by **Michael Bodnarchuk**.

License: **MIT**.

Powered by [Go! Aspect-Oriented Framework](http://go.aopphp.com/)
