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
class Registry {

    protected static $classCalls = [];
    protected static $instanceCalls = [];
    protected static $ns = '';

    /**
     * @var Mocker
     */
    public static $mocker;

    static function registerClass($name, $params = array())
    {
        self::$mocker->registerClass($name, $params);
    }

    static function registerObject($object, $params = array())
    {
        self::$mocker->registerObject($object, $params);
    }

    static function registerFunc($func, $resultOrClosure)
    {
        self::$mocker->registerFunc($func, $resultOrClosure);
    }

    static function getClassCallsFor($class)
    {
        return isset(self::$classCalls[$class])
            ? self::$classCalls[$class]
            : [];
    }

    static function getInstanceCallsFor($instance)
    {
        $oid = spl_object_hash($instance);
        return isset(self::$instanceCalls[$oid])
            ? self::$instanceCalls[$oid]
            : [];
    }

    static function clean($classOrInstance = null)
    {
        $classOrInstance = self::getRealClassOrObject($classOrInstance);
        self::$mocker->clean($classOrInstance);
        if (is_object($classOrInstance)) {
            $oid = spl_object_hash($classOrInstance);
            unset(self::$instanceCalls[$oid]);

        } elseif (is_string($classOrInstance)) {
            unset(self::$classCalls[$classOrInstance]);

        } else {
            self::$ns = '';
            self::cleanInvocations();
        }
    }

    static function cleanInvocations()
    {
        self::$instanceCalls = [];
        self::$classCalls = [];
    }

    static function registerInstanceCall($instance, $method, $args = array())
    {
        $oid = spl_object_hash($instance);
        if (!isset(self::$instanceCalls[$oid])) self::$instanceCalls[$oid] = [];

        isset(self::$instanceCalls[$oid][$method])
            ? self::$instanceCalls[$oid][$method][] = $args
            : self::$instanceCalls[$oid][$method] = array($args);

    }

    static function registerClassCall($class, $method, $args = array())
    {
        if (!isset(self::$classCalls[$class])) self::$classCalls[$class] = [];

        isset(self::$classCalls[$class][$method])
            ? self::$classCalls[$class][$method][] = $args
            : self::$classCalls[$class][$method] = array($args);

    }

    public static function getRealClassOrObject($classOrObject)
    {
        if ($classOrObject instanceof ClassProxy) return $classOrObject->className;
        if ($classOrObject instanceof InstanceProxy) return $classOrObject->getObject();
        return $classOrObject;
    }

    /**
     * @param mixed $mocker
     */
    public static function setMocker(Mocker $mocker)
    {
        self::$mocker = $mocker;
    }

    public static function setNamespace($namespace)
    {
        self::$ns = $namespace;
    }

    public static function getNamespace()
    {
        return self::$ns;
    }

}