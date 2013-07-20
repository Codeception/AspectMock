<?php
namespace demo;

class UserService__AopProxied {

    public function create($data = array())
    {
        $user = new UserModel($data);
        $user->save();
    }

    public function updateName(UserModel $user)
    {
        $user->setName("Current User");
        $user->save();
    }
}
class UserService extends UserService__AopProxied implements \Go\Aop\Proxy, \AspectMock\Invocation\Verifiable
{
    use \AspectMock\Invocation\Verify;

    /**
     *Property was created automatically, do not change it manually
     */
    private static $__joinPoints = array();
    
    
    public function create($data = array())
    {
        return self::$__joinPoints['method:create']->__invoke($this, array($data));
    }
    
    
    public function updateName(\demo\UserModel $user)
    {
        return self::$__joinPoints['method:updateName']->__invoke($this, array($user));
    }
    
}
\Go\Proxy\ClassProxy::injectJoinPoints('demo\UserService', unserialize('a:3:{s:13:"method:create";a:2:{i:0;C:40:"Go\\Aop\\Framework\\MethodAroundInterceptor":132:{a:1:{s:12:"adviceMethod";a:3:{s:5:"scope";s:6:"aspect";s:6:"method";s:11:"stubMethods";s:6:"aspect";s:22:"AspectMock\\Core\\Mocker";}}}i:1;C:39:"Go\\Aop\\Framework\\MethodAfterInterceptor":140:{a:1:{s:12:"adviceMethod";a:3:{s:5:"scope";s:6:"target";s:6:"method";s:19:"registerMethodCalls";s:6:"aspect";s:22:"AspectMock\\Core\\Mocker";}}}}s:17:"method:updateName";a:2:{i:0;r:3;i:1;r:9;}s:45:"introduction:AspectMock\\Invocation\\Verifiable";O:38:"Go\\Aop\\Framework\\TraitIntroductionInfo":2:{s:59:"' . "\0" . 'Go\\Aop\\Framework\\TraitIntroductionInfo' . "\0" . 'introducedInterface";s:32:"AspectMock\\Invocation\\Verifiable";s:59:"' . "\0" . 'Go\\Aop\\Framework\\TraitIntroductionInfo' . "\0" . 'implementationClass";s:28:"AspectMock\\Invocation\\Verify";}}'));