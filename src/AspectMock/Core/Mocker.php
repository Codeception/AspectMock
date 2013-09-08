<?php
namespace AspectMock\Core;
use Go\Aop\Aspect;
use AspectMock\Intercept\MethodInvocation;

class Mocker implements Aspect {

    protected $classMap = [];
    protected $objectMap = [];
    protected $funcMap = [];
    protected $methodMap = ['__call', '__callStatic'];

    public function fakeMethodsAndRegisterCalls(MethodInvocation $invocation)
    {
        $obj = $invocation->getThis();
        $method = $invocation->getMethod();
        
        $result = $this->invokeFakedMethods($invocation);

        if (is_object($obj)) {
            if (isset($this->objectMap[spl_object_hash($obj)])) Registry::registerInstanceCall($obj, $method, $invocation->getArguments(), $result);
            $class = get_class($obj);
        } else {
            $class = $obj;
        }
        if (isset($this->classMap[$class])) Registry::registerClassCall($class, $method, $invocation->getArguments(), $result);
        return $result;
    }

    protected function invokeFakedMethods(MethodInvocation $invocation)
    {
        $method = $invocation->getMethod();
        if (!in_array($method, $this->methodMap)) return __AM_CONTINUE__;

        $obj = $invocation->getThis();

        if (is_object($obj)) {
            // instance method
            $params = $this->getObjectMethodStubParams($obj, $method);
            if ($params !== false) return $this->stub($invocation, $params);

            // class method
            $params = $this->getClassMethodStubParams(get_class($obj), $method);
            if ($params !== false) return $this->stub($invocation, $params);

            // inheritance
            $params = $this->getClassMethodStubParams($invocation->getDeclaredClass(), $method);
            if ($params !== false) return $this->stub($invocation, $params);

            // magic methods
            if ($method == '__call') {
                $args = $invocation->getArguments();
                $method = array_shift($args);

                $params = $this->getObjectMethodStubParams($obj, $method);
                if ($params !== false) return $this->stubMagicMethod($invocation, $params);

                // magic class method
                $params = $this->getClassMethodStubParams(get_class($obj), $method);
                if ($params !== false) return $this->stubMagicMethod($invocation, $params);

                // inheritance
                $calledClass = $this->getRealClassName($invocation->getDeclaredClass());
                $params = $this->getClassMethodStubParams($calledClass, $method);
                if ($params !== false) return $this->stubMagicMethod($invocation, $params);
            }
        } else {
            // static method
            $params = $this->getClassMethodStubParams($obj, $method);
            if ($params !== false) return $this->stub($invocation, $params);

            // magic static method (facade)
            if ($method == '__callStatic') {
                $args = $invocation->getArguments();
                $method = array_shift($args);

                $params = $this->getClassMethodStubParams($obj, $method);
                if ($params !== false) return $this->stubMagicMethod($invocation, $params);

                // inheritance
                $calledClass = $this->getRealClassName($invocation->getMethod()->getDeclaringClass());
                $params = $this->getClassMethodStubParams($calledClass, $method);
                if ($params !== false) return $this->stubMagicMethod($invocation, $params);
            }
        }
        return __AM_CONTINUE__;
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
        $name = $invocation->getMethod();

        $replacedMethod = $params[$name];

        $replacedMethod = $this->turnToClosure($replacedMethod);

        if ($invocation->isStatic()) {
            \Closure::bind($replacedMethod, null, $invocation->getThis());
        } else {
            $replacedMethod = $replacedMethod->bindTo($invocation->getThis(), get_class($invocation->getThis()));
        }
        return call_user_func_array($replacedMethod, $invocation->getArguments());
    }

    protected function stubMagicMethod(MethodInvocation $invocation, $params)
    {
        $args = $invocation->getArguments();
        $name = array_shift($args);

        $replacedMethod = $params[$name];
        $replacedMethod = $this->turnToClosure($replacedMethod);

        if ($invocation->isStatic()) {
            \Closure::bind($replacedMethod, null, $invocation->getThis());
        } else {
            $replacedMethod = $replacedMethod->bindTo($invocation->getThis(), get_class($invocation->getThis()));
        }
        return call_user_func_array($replacedMethod, $args);
    }


    protected function turnToClosure($returnValue)
    {
        if ($returnValue instanceof \Closure) return $returnValue;
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
        $this->methodMap = array_merge($this->methodMap, array_keys($params));
        $this->classMap[$class] = $params;
    }

    public function registerObject($object, $params = array())
    {
        $hash = spl_object_hash($object);
        if (isset($this->objectMap[$hash])) {
            $params = array_merge($this->objectMap[$hash], $params);
        }
        $this->objectMap[$hash] = $params;
        $this->methodMap = array_merge($this->methodMap, array_keys($params));
    }

    public function clean($objectOrClass = null)
    {
        if (!$objectOrClass) {
            $this->classMap = [];
            $this->objectMap = [];
            $this->funcMap = [];
        } elseif (is_object($objectOrClass)) {
            unset($this->objectMap[spl_object_hash($objectOrClass)]);
        } else {
            unset($this->classMap[$objectOrClass]);
        }
    }

    private function getRealClassName($class)
    {
        return str_replace('__AopProxied','', $class->name);
    }
}
