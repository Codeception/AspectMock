<?php

namespace AspectMock\Core;

use AspectMock\Proxy\ClassProxy;
use AspectMock\Proxy\InstanceProxy;

/**
 * Used to store tracked classes and objects.
 *
 * Class Registry
 * @package AspectMock
 */
class Registry
{

    /**
     * @var array
     */
    protected static $classCalls = [];

    /**
     * @var array
     */
    protected static $instanceCalls = [];

    /**
     * @var array
     */
    protected static $funcCalls = [];

    /**
     * @var Mocker
     */
    public static $mocker;

    /**
     * @param $name
     * @param array $params
     */
    public static function registerClass($name, $params = array())
    {
        self::$mocker->registerClass($name, $params);
    }

    /**
     * @param $object
     * @param array $params
     */
    public static function registerObject($object, $params = array())
    {
        self::$mocker->registerObject($object, $params);
    }

    /**
     * @param $namespace
     * @param $function
     * @param $resultOrClosure
     */
    public static function registerFunc($namespace, $function, $resultOrClosure)
    {
        self::$mocker->registerFunc($namespace, $function, $resultOrClosure);
    }

    /**
     * @param $class
     * @return array|mixed
     */
    public static function getClassCallsFor($class)
    {
        $class = ltrim($class, '\\');
        return isset(self::$classCalls[$class])
            ? self::$classCalls[$class]
            : [];
    }

    /**
     * @param $instance
     * @return array|mixed
     */
    public static function getInstanceCallsFor($instance)
    {
        $oid = spl_object_hash($instance);
        return isset(self::$instanceCalls[$oid])
            ? self::$instanceCalls[$oid]
            : [];
    }

    /**
     * @param $func
     * @return array|mixed
     */
    public static function getFuncCallsFor($func)
    {
        $func = ltrim($func, '\\');
        return isset(self::$funcCalls[$func]) ? self::$funcCalls[$func] : [];
    }

    /**
     * @param null $classOrInstance
     */
    public static function clean($classOrInstance = null)
    {
        $classOrInstance = self::getRealClassOrObject($classOrInstance);
        self::$mocker->clean($classOrInstance);
        if (is_object($classOrInstance)) {
            $oid = spl_object_hash($classOrInstance);
            unset(self::$instanceCalls[$oid]);

        } elseif (is_string($classOrInstance)) {
            unset(self::$classCalls[$classOrInstance]);

        } else {
            self::cleanInvocations();
        }
    }

    /**
     *
     */
    public static function cleanInvocations()
    {
        self::$instanceCalls = [];
        self::$classCalls = [];
        self::$funcCalls = [];
    }

    /**
     * @param $instance
     * @param $method
     * @param array $args
     */
    public static function registerInstanceCall($instance, $method, $args = array())
    {
        $oid = spl_object_hash($instance);
        if (!isset(self::$instanceCalls[$oid])) {
            self::$instanceCalls[$oid] = [];
        }

        isset(self::$instanceCalls[$oid][$method])
            ? self::$instanceCalls[$oid][$method][] = $args
            : self::$instanceCalls[$oid][$method] = array($args);

    }

    /**
     * @param $class
     * @param $method
     * @param array $args
     */
    public static function registerClassCall($class, $method, $args = array())
    {
        if (!isset(self::$classCalls[$class])) {
            self::$classCalls[$class] = [];
        }

        isset(self::$classCalls[$class][$method])
            ? self::$classCalls[$class][$method][] = $args
            : self::$classCalls[$class][$method] = array($args);

    }

    /**
     * @param $functionName
     * @param $args
     */
    public static function registerFunctionCall($functionName, $args)
    {
        if (!isset(self::$funcCalls[$functionName])) {
            self::$funcCalls[$functionName] = [];
        }

        isset(self::$funcCalls[$functionName])
            ? self::$funcCalls[$functionName][] = $args
            : self::$funcCalls[$functionName] = array($args);
    }

    /**
     * @param $classOrObject
     * @return mixed
     */
    public static function getRealClassOrObject($classOrObject)
    {
        if ($classOrObject instanceof ClassProxy) {
            return $classOrObject->className;
        }
        if ($classOrObject instanceof InstanceProxy) {
            return $classOrObject->getObject();
        }
        return $classOrObject;
    }

    /**
     * @param mixed $mocker
     */
    public static function setMocker(Mocker $mocker)
    {
        self::$mocker = $mocker;
    }

}
