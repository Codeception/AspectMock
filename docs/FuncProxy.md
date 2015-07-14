
## AspectMock\Proxy\FuncProxy



FuncProxy is a wrapper around mocked function, used to verify function calls.
Has the same verification methods as `InstanceProxy` and `ClassProxy` do.

Usage:

```php
<?php
namespace Acme\User;
$func = test::func('Acme\User', 'strlen', 10);
strlen('hello');
strlen('world');
$func->verifyInvoked(); // true
$func->verifyInvoked(['hello']); // true
$func->verifyInvokedMultipleTimes(2);
$func->verifyNeverInvoked(['bye']);

```


#### *public* verifyInvoked($params = null) 
 * `param null` $params

#### *public* verifyInvokedOnce($params = null) 
 * `param null` $params

#### *public* verifyNeverInvoked($params = null) 
 * `param null` $params

#### *public* verifyInvokedMultipleTimes($times, $params = null) 
 * `param` $times
 * `param null` $params

#### *public* getCallsForMethod($func) 

