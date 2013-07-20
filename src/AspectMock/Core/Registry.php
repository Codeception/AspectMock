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

    protected static $classCalls;

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

    static function clean()
    {
        self::getMockAspect()->clean();
        self::$classCalls = [];       
    }
    
    static function registerClassCall($class, $method, $args = array())
    {
        if (!isset(self::$classCalls[$class])) self::$classCalls[$class] = [];

        isset(self::$classCalls[$class][$method])
            ? self::$classCalls[$class][$method][] = $args
            : self::$classCalls[$class][$method] = array($args);
           
    }



}