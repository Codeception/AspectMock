<?php
namespace AspectMock\Core;
use AspectMock\Invocation\Verify;
use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\DeclareParents;

class Mocker implements Aspect {

    protected $classMap = [];
    protected $objectMap = [];
    protected $classCalls = [];

    /**
     * @DeclareParents(value="**", interface="AspectMock\Invocation\Verifiable", defaultImpl="AspectMock\Invocation\Verify")
     *
     * @var null
     */
    protected $introduction = null;

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
     * @After("within(**)", scope="target")
     */
    public function registerMethodCalls(MethodInvocation $invocation)
    {
        $obj = $invocation->getThis();
        $method = $invocation->getMethod()->name;
        if (is_object($obj)) {
            isset($obj->__calls[$method])
                ? $obj->__calls[$method][] = $invocation->getArguments()
                : $obj->__calls[$method] = array($invocation->getArguments());
            $class = get_class($obj);
        } else {
            $class = $obj;
        }
        Registry::registerClassCall($class, $method, $invocation->getArguments());
    }

    protected function getObjectMethodStubParams($obj, $method_name)
    {
        $oid = spl_object_hash($obj);
        if (!isset($this->objectMap[$oid])) return false;
        $params = $this->objectMap[$oid];
        if (!array_key_exists($method_name,$params)) return false;
        return $params[$method_name];
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
        $this->classMap[$class] = $params;
    }

    public function registerObject($object, $params = array())
    {
        $this->objectMap[spl_object_hash($object)] = $params;
    }

    public function clean()
    {
        $this->classMap = [];
        $this->objectMap = [];
        $this->classCalls = [];
    }
}