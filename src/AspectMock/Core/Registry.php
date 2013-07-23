<?php
namespace AspectMock\Core;
use AspectMock\Kernel;

/**
 * Used to store tracked classes and objects.
 *
 * Class Registry
 * @package AspectMock
 */
class Registry {

    protected static $classCalls = [];
    protected static $instanceCalls = [];
    protected static $returned = [];

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


    static function clean()
    {
        self::getMockAspect()->clean();
        self::$classCalls = [];
        self::$instanceCalls = [];
    }

    static function registerInstanceCall($instance, $method, $args = array(), $returned = null)
    {
        $oid = spl_object_hash($instance);
        if (!isset(self::$instanceCalls[$oid])) self::$instanceCalls[$oid] = [];

        isset(self::$instanceCalls[$oid][$method])
            ? self::$instanceCalls[$oid][$method][] = $args
            : self::$instanceCalls[$oid][$method] = array($args);

        self::$returned["$oid->$method"] = $returned;
        
    }

    static function registerClassCall($class, $method, $args = array(), $returned = null)
    {
        if (!isset(self::$classCalls[$class])) self::$classCalls[$class] = [];

        isset(self::$classCalls[$class][$method])
            ? self::$classCalls[$class][$method][] = $args
            : self::$classCalls[$class][$method] = array($args);

        self::$returned["$class.$method"] = $returned;
    }



}