
## AspectMock\Test



`AspectMock\Test` class is a builder of test doubles.
Any object can be enhanced and turned to a test double with the call to `double` method.
Mocking abstract classes and interfaces is not supported at this time.
This allows to redefine any method of object with your own, and adds mock verification methods.

**Recommended Usage**:

``` php
<?php
use AspectMock\Test as test;
?>
```

#### *public static* double($classOrObject, array $params = Array ( ) ) 
`test::double` registers class or object to track its calls.
In second argument you may pass values that mocked mathods should return.

Returns either of [**ClassProxy**](https://github.com/Codeception/AspectMock/blob/master/docs/ClassProxy.md) (when a string was passed)
or [**InstanceProxy**](https://github.com/Codeception/AspectMock/blob/master/docs/InstanceProxy.md) (when an object was passed).
Proxies are used to verify method invocations, and some other useful things (check out the links above for more).

Examples:

``` php
<?php

# simple
$user = test::double(new User, ['getName' => 'davert']);
$user->getName() // => davert
$user->verifyInvoked('getName'); // => success
$user->getObject() // => returns instance of User, i.e. real, not proxified object

# with closure
$user = test::double(new User, ['getName' => function() { return $this->login; }]);
$user->login = 'davert';
$user->getName(); // => davert

# on a class
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

# append declaration
$user = new User;
test::double($user, ['getName' => 'davert']);
test::double($user, ['getEmail' => 'davert@mail.ua']);
$user->getName(); // => 'davert'
$user->getEmail(); => 'davert@mail.ua'

# create an instance of mocked class
test::double('User')->construct(['name' => 'davert']); // via constructir
test::double('User')->make(); // without calling constructor

# stub for magic method
test::double('User', ['findByUsernameAndPasswordAndEmail' => false]);
User::findByUsernameAndPasswordAndEmail(); // null

# stub for method of parent class
# if User extends ActiveRecord
test::double('ActiveRecord', ['save' => false]);
$user = new User(['name' => 'davert']);
$user->save(); // false

?>
```

 * api
 * `param string|object` $classOrObject
 * `param array` $params [ 'methodName' => 'returnValue' ]
 * throws \Exception
 * return Verifier Usually Proxy\ClassProxy|Proxy\InstanceProxy

#### *public static* spec($classOrObject, array $params = Array ( ) ) 
If you follow TDD/BDD practices a test should be written before the class is defined.
If you would call undefined class in a test, a fatal error will be triggered.
Instead you can use `test::spec` method that will create a proxy for an undefined class.

``` php
<?php
$userClass = test::spec('User');
$userClass->defined(); // false
?>
```

You can create instances of undefined classes and play with them:

``` php
<?php
$user = test::spec('User')->construct();
$user->setName('davert');
$user->setNumPosts(count($user->getPosts()));
$this->assertEquals('davert', $user->getName()); // fail
?>
```

The test will be executed normally and will fail at the first assertion.

`test::spec()->construct` creates an instance of `AspectMock\Proxy\Anything`
which tries not to cause errors whatever you try to do with it.

``` php
<?php
$user = test::spec('Undefined')->construct();
$user->can()->be->called()->anything();
$user->can['be used as array'];
foreach ($user->names as $name) {
     $name->canBeIterated();
}
?>
```

None of those calls will trigger an error in your test.
Thus, you can write a valid test before the class is declared.

If class is already defined, `test::spec` will act as `test::double`.

 * api
 * `param string|object` $classOrObject
 * `param array` $params
 * return Verifier Usually Proxy\ClassProxy|Proxy\InstanceProxy

#### *public static* methods($classOrObject, array $only = Array ( ) ) 
Replaces all methods in a class with dummies, except those specified in the `$only` param.

``` php
<?php
$user = new User(['name' => 'jon']);
test::methods($user, ['getName']);
$user->setName('davert'); // not invoked
$user->getName(); // jon
?>
```

You can create a dummy without a constructor with all methods disabled:

``` php
<?php
$user = test::double('User')->make();
test::methods($user, []);
?>
```

 * api
 * `param string|object` $classOrObject
 * `param string[]` $only
 * return Verifier Usually Proxy\ClassProxy|Proxy\InstanceProxy
 * throws \Exception

#### *public static* func($namespace, $functionName, $body) 
Replaces function in provided namespace with user-defined function or value that function returns.
Function is restored to original on cleanup.

```php
<?php
namespace demo;
test::func('demo', 'date', 2004);
date('Y'); // 2004

test::func('demo', 'date', function($format) {
   if ($format == 'Y') {
     return 2004;
   } else {
     return \date($param);
   }
}

```

Mocked functions can be verified for calls:

```php
<?php
namespace demo;
$func = test::func('demo', 'date', 2004);
date('Y'); // 2004
$func->verifyInvoked();
$func->verifyInvokedOnce(['Y']);
```

 * `param string` $namespace
 * `param string` $functionName
 * `param mixed` $body whatever a function might return or Callable substitute
 * return Proxy\FuncProxy

#### *public static* clean($classOrInstance = null) 
Clears test doubles registry.
Should be called between tests.

``` php
<?php
test::clean();
?>
```

Also you can clean registry only for the specific class or object.

``` php
<?php
test::clean('User');
test::clean($user);
?>
```

 * api
 * `param string|object` $classOrObject
 * return void

#### *public static* cleanInvocations() 
Clears mock verifications but not stub definitions.

 * api
 * return void


