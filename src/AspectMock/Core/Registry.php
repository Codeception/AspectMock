<?php

declare(strict_types=1);

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
    protected static array $classCalls = [];

    protected static array $instanceCalls = [];

    protected static array $funcCalls = [];

    public static ?Mocker $mocker = null;

    public static function registerClass($name, $params = array()): void
    {
        self::$mocker->registerClass($name, $params);
    }

    public static function registerObject($object, $params = array()): void
    {
        self::$mocker->registerObject($object, $params);
    }

    public static function registerFunc($namespace, $function, $resultOrClosure): void
    {
        self::$mocker->registerFunc($namespace, $function, $resultOrClosure);
    }

    public static function getClassCallsFor($class)
    {
        $class = ltrim($class,'\\');
        return self::$classCalls[$class] ?? [];
    }

    public static function getInstanceCallsFor($instance)
    {
        $oid = spl_object_hash($instance);
        return self::$instanceCalls[$oid] ?? [];
    }

    public static function getFuncCallsFor($func)
    {
        $func = ltrim($func,'\\');
        return self::$funcCalls[$func] ?? [];
    }

    public static function clean($classOrInstance = null): void
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

    public static function cleanInvocations(): void
    {
        self::$instanceCalls = [];
        self::$classCalls = [];
        self::$funcCalls = [];
    }

    public static function registerInstanceCall($instance, $method, $args = array()): void
    {
        $oid = spl_object_hash($instance);
        if (!isset(self::$instanceCalls[$oid])) self::$instanceCalls[$oid] = [];

        isset(self::$instanceCalls[$oid][$method])
            ? self::$instanceCalls[$oid][$method][] = $args
            : self::$instanceCalls[$oid][$method] = array($args);

    }

    public static function registerClassCall($class, $method, $args = array()): void
    {
        if (!isset(self::$classCalls[$class])) self::$classCalls[$class] = [];

        isset(self::$classCalls[$class][$method])
            ? self::$classCalls[$class][$method][] = $args
            : self::$classCalls[$class][$method] = array($args);

    }

    public static function registerFunctionCall($functionName, $args): void
    {
        if (!isset(self::$funcCalls[$functionName])) self::$funcCalls[$functionName] = [];

        isset(self::$funcCalls[$functionName])
            ? self::$funcCalls[$functionName][] = $args
            : self::$funcCalls[$functionName] = array($args);
    }

    public static function getRealClassOrObject($classOrObject)
    {
        if ($classOrObject instanceof ClassProxy) return $classOrObject->className;
        
        if ($classOrObject instanceof InstanceProxy) return $classOrObject->getObject();
        
        return $classOrObject;
    }

    public static function setMocker(Mocker $mocker): void
    {
        self::$mocker = $mocker;
    }
}
