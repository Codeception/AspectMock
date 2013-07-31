<?php
namespace AspectMock\Core;
use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;

class Mocker implements Aspect {

    protected $classMap = [];
    protected $objectMap = [];

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

    /**
     * @Around("within(**)")
     */
    public function registerMethodCalls(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();
        $obj = $invocation->getThis();
        $method = $invocation->getMethod()->name;
        if (is_object($obj)) {
            Registry::registerInstanceCall($obj, $method, $invocation->getArguments(), $result);
            $class = get_class($obj);
        } else {
            $class = $obj;
        }
        Registry::registerClassCall($class, $method, $invocation->getArguments(), $result);
        return $result;
    }

    protected function getObjectMethodStubParams($obj, $method_name)
    {
        $oid = spl_object_hash($obj);
        if (!isset($this->objectMap[$oid])) return false;
        $params = $this->objectMap[$oid];
        if (!array_key_exists($method_name,$params)) return false;
        return $params;
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

    public function registerClass($class, $params = array())
    {
        $class = ltrim($class,'\\');
        if (isset($this->classMap[$class])) {
            $params = array_merge($this->classMap[$class], $params);
        }
        $this->classMap[$class] = $params;
    }

    public function registerObject($object, $params = array())
    {
        $hash = spl_object_hash($object);
        if (isset($this->objectMap[$hash])) {
            $params = array_merge($this->objectMap[$hash], $params);
        }
        $this->objectMap[$hash] = $params;
    }

    public function clean($objectOrClass = null)
    {
        if (!$objectOrClass) {
            $this->classMap = [];
            $this->objectMap = [];
        } elseif (is_object($objectOrClass)) {
            unset($this->objectMap[spl_object_hash($objectOrClass)]);
        } else {
            unset($this->classMap[$objectOrClass]);
        }
    }
}