<?php
namespace AspectMock\Core;
use AspectMock\Kernel;
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
    protected static $classReturned = [];
    protected static $instanceReturned = [];

    /**
     * @return Mock
     */
    protected static function getMockAspect()
    {
        return Kernel::getInstance()->getContainer()->getAspect('AspectMock\Core\Mocker');
    }

    static function registerClass($name, $params = array())
    {
        self::getMockAspect()->registerClass($name, $params);
    }

    static function registerObject($object, $params = array())
    {
        self::getMockAspect()->registerObject($object, $params);
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

    static function getReturnedValues($classOrInstance, $method)
    {
        $classOrInstance = self::getRealClassOrObject($classOrInstance);
        if (is_object($classOrInstance)) {
            $oid = spl_object_hash($classOrInstance);
            if (!isset(self::$instanceReturned[$oid])) return array();
            $storage = self::$instanceReturned[$oid];
        } else {
            if (!isset(self::$classReturned[$classOrInstance])) return array();
            $storage = self::$classReturned[$classOrInstance];
        }

        if (!isset($storage[$method])) return array();

        return $storage[$method];
    }

    static function clean($classOrInstance = null)
    {
        $classOrInstance = self::getRealClassOrObject($classOrInstance);
        self::getMockAspect()->clean($classOrInstance);
        if (is_object($classOrInstance)) {
            $oid = spl_object_hash($classOrInstance);
            unset(self::$instanceCalls[$oid]);
            unset(self::$instanceReturned[$oid]);

        } elseif (is_string($classOrInstance)) {
            unset(self::$classCalls[$classOrInstance]);
            unset(self::$classReturned[$classOrInstance]);

        } else {
            self::$instanceCalls = [];
            self::$classCalls = [];
            self::$classReturned = [];
            self::$instanceReturned = [];
        }
    }

    static function registerInstanceCall($instance, $method, $args = array(), $returned = null)
    {
        $oid = spl_object_hash($instance);
        if (!isset(self::$instanceCalls[$oid])) self::$instanceCalls[$oid] = [];

        isset(self::$instanceCalls[$oid][$method])
            ? self::$instanceCalls[$oid][$method][] = $args
            : self::$instanceCalls[$oid][$method] = array($args);

        if (!isset(self::$instanceReturned[$oid])) self::$instanceReturned[$oid] = [];

        isset(self::$instanceReturned[$method])
            ? self::$instanceReturned[$method][] = $returned
            : self::$instanceReturned[$method] = array($returned);
    }

    static function registerClassCall($class, $method, $args = array(), $returned = null)
    {
        if (!isset(self::$classCalls[$class])) self::$classCalls[$class] = [];

        isset(self::$classCalls[$class][$method])
            ? self::$classCalls[$class][$method][] = $args
            : self::$classCalls[$class][$method] = array($args);

        if (!isset(self::$classReturned[$class])) self::$classReturned[$class] = [];

        isset(self::$classReturned[$class][$method])
            ? self::$classReturned[$class][$method][] = $returned
            : self::$classReturned[$class][$method] = array($returned);

    }

    public static function getRealClassOrObject($classOrObject)
    {
        if ($classOrObject instanceof ClassProxy) return $classOrObject->className;
        if ($classOrObject instanceof InstanceProxy) return $classOrObject->getObject();
        return $classOrObject;
    }



}