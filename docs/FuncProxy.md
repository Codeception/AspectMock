
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

#### *public* verifyInvoked(array $params = null) 
#### *public* verifyInvokedOnce(array $params = null) 
#### *public* verifyNeverInvoked(array $params = null) 
#### *public* verifyInvokedMultipleTimes($times, array $params = null) 
#### *public* getCallsForMethod($func) 

