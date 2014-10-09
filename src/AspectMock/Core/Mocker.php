<?php
namespace AspectMock\Core;
use AspectMock\Intercept\FunctionInjector;
use Go\Aop\Aspect;
use AspectMock\Intercept\MethodInvocation;

class Mocker implements Aspect {

    protected $classMap = [];
    protected $objectMap = [];
    protected $funcMap = [];
    protected $methodMap = ['__call', '__callStatic'];

    public function fakeMethodsAndRegisterCalls($class, $declaredClass, $method, $params, $static)
    {
//        $method = $invocation->getMethod();
//        $obj = $invocation->getThis();
        $result = __AM_CONTINUE__;

        if (in_array($method, $this->methodMap)) {
            $invocation = new \AspectMock\Intercept\MethodInvocation();
            $invocation->setThis($class);
            $invocation->setMethod($method);
            $invocation->setArguments($params);
            $invocation->isStatic($static);
            $invocation->setDeclaredClass($declaredClass);
            $result = $this->invokeFakedMethods($invocation);
        }

        if (!$static) {
            if (isset($this->objectMap[spl_object_hash($class)])) Registry::registerInstanceCall($class, $method, $params);
            $class = get_class($class);
        }

        if (isset($this->classMap[$class])) Registry::registerClassCall($class, $method, $params);
        return $result;
    }

    public function fakeFunctionAndRegisterCalls($namespace, $function, $args)
    {
        $result = __AM_CONTINUE__;
        $fullFuncName = "$namespace\\$function";
        Registry::registerFunctionCall($fullFuncName, $args);

        if (isset($this->funcMap[$fullFuncName])) {
            $func = $this->funcMap[$fullFuncName];
            if (is_callable($func)) {
                $result = call_user_func_array($func, $args);
            } else {
                $result = $func;
            }
        }
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
                $calledClass = $invocation->getDeclaredClass();
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
                $calledClass = $invocation->getDeclaredClass();
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

    public function registerFunc($namespace, $func, $body)
    {
        if (!function_exists("$namespace\\$func")) {
            $injector = new FunctionInjector($namespace, $func);
            $injector->save();
            $injector->inject();
        }
        $this->funcMap["$namespace\\$func"] = $body;
    }

    public function clean($objectOrClass = null)
    {
        if (!$objectOrClass) {
            $this->classMap = [];
            $this->objectMap = [];
            $this->methodMap = ['__call','__callStatic'];
            $this->funcMap = [];
        } elseif (is_object($objectOrClass)) {
            unset($this->objectMap[spl_object_hash($objectOrClass)]);
        } else {
            unset($this->classMap[$objectOrClass]);
        }
    }

}
