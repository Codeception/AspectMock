<?php
namespace demo;

class UserModel__AopProxied {

    protected $name;

    static function tableName()
    {
        return "users";
    }

    public function __construct($data = array())
    {
        foreach ($data as $key => $value)
        {
            $this->$key = $value;
        }
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    function save()
    {
        throw new \PHPUnit_Framework_AssertionFailedError("I should not be called");
    }

}
class UserModel extends UserModel__AopProxied implements \Go\Aop\Proxy, \AspectMock\Invocation\Verifiable
{
    use \AspectMock\Invocation\Verify;

    /**
     *Property was created automatically, do not change it manually
     */
    private static $__joinPoints = array();
    
    
    public function __construct($data = array())
    {
        return self::$__joinPoints['method:__construct']->__invoke($this, array($data));
    }
    
    /**
     * @return mixed
     */
    public function getName()
    {
        return self::$__joinPoints['method:getName']->__invoke($this);
    }
    
    
    public function save()
    {
        return self::$__joinPoints['method:save']->__invoke($this);
    }
    
    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        return self::$__joinPoints['method:setName']->__invoke($this, array($name));
    }
    
    
    public static function tableName()
    {
        return self::$__joinPoints['static:tableName']->__invoke(get_called_class());
    }
    
}
\Go\Proxy\ClassProxy::injectJoinPoints('demo\UserModel', unserialize('a:6:{s:16:"static:tableName";a:2:{i:0;C:40:"Go\\Aop\\Framework\\MethodAroundInterceptor":132:{a:1:{s:12:"adviceMethod";a:3:{s:5:"scope";s:6:"aspect";s:6:"method";s:11:"stubMethods";s:6:"aspect";s:22:"AspectMock\\Core\\Mocker";}}}i:1;C:39:"Go\\Aop\\Framework\\MethodAfterInterceptor":140:{a:1:{s:12:"adviceMethod";a:3:{s:5:"scope";s:6:"target";s:6:"method";s:19:"registerMethodCalls";s:6:"aspect";s:22:"AspectMock\\Core\\Mocker";}}}}s:18:"method:__construct";a:2:{i:0;r:3;i:1;r:9;}s:14:"method:setName";a:2:{i:0;r:3;i:1;r:9;}s:14:"method:getName";a:2:{i:0;r:3;i:1;r:9;}s:11:"method:save";a:2:{i:0;r:3;i:1;r:9;}s:45:"introduction:AspectMock\\Invocation\\Verifiable";O:38:"Go\\Aop\\Framework\\TraitIntroductionInfo":2:{s:59:"' . "\0" . 'Go\\Aop\\Framework\\TraitIntroductionInfo' . "\0" . 'introducedInterface";s:32:"AspectMock\\Invocation\\Verifiable";s:59:"' . "\0" . 'Go\\Aop\\Framework\\TraitIntroductionInfo' . "\0" . 'implementationClass";s:28:"AspectMock\\Invocation\\Verify";}}'));