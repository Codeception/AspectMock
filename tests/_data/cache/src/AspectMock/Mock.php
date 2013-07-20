<?php
namespace AspectMock;

use AspectMock\Registry;
use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Before;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\Pointcut;

class Mock implements Aspect {

    use Mock\Registry;

    protected $classMap = [];
    protected $objectMap = [];

    protected $objectMethodCalls = [];
    protected $classMethodCalls = [];

    public static $magic = array('__isset', '__get', '__set');

    /**
     * @Around("within(**)")
     */
    public function stubMethods(MethodInvocation $invocation)
    {
        $obj = $invocation->getThis();
        if (is_object($obj)) {
            $params = $this->getObjectMethodStubParams($obj, $invocation->getMethod()->name);
            if ($params !== false) return $this->stub($invocation, $params);

            $params = $this->getClassMethodStubParams(get_class($obj), $invocation->getMethod()->name);
            if ($params !== false) return $this->stub($invocation, $params);
        } else {
            $params = $this->getClassMethodStubParams($obj, $invocation->getMethod()->name);
            if ($params !== false) return $this->stub($invocation, $params);
        }
        return $invocation->proceed();
    }

    protected function getObjectMethodStubParams($obj, $method_name)
    {
        $oid = spl_object_hash($obj);
        if (!isset($this->objectMap[$oid])) return false;
        $params = $this->objectMap[$oid];
        $this->incrementMethodCalls($this->objectMethodCalls[$oid], $method_name);
        if (!array_key_exists($method_name,$params)) return false;
        return $params[$method_name];
    }

    protected function incrementMethodCalls($counter, $method)
    {
        isset($counter[$method]) ? $counter[$method]++ : $counter[$method] = 1; 
    }   

    protected function getClassMethodStubParams($class_name, $method_name)
    {
        if (!isset($this->classMap[$class_name])) return false;
        $params = $this->classMap[$class_name];
        if (!array_key_exists($method_name,$params)) return false;
        return $params;
    }
    
    protected function stub(MethodInvocation $invocation, $params)
    {
        $name = $invocation->getMethod()->name;

        $replacedMethod = $params[$name];

        if (!($replacedMethod instanceof \Closure)) $replacedMethod = $this->turnToClosure($replacedMethod);

        if ($invocation->getMethod()->isStatic()) {
            \Closure::bind($replacedMethod, null, $invocation->getThis());
        } else {
            $replacedMethod = $replacedMethod->bindTo($invocation->getThis(), get_class($invocation->getThis()));
        }
        $invocation->getArguments();
        return call_user_func_array($replacedMethod, $invocation->getArguments());
    }

    protected function turnToClosure($returnValue)
    {
        return function() use ($returnValue) {
            return $returnValue;
        };
    }
}