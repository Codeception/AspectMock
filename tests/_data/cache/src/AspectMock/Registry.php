<?php
namespace AspectMock;

/**
 * Used to store tracked classes and objects.
 *
 * Class Registry
 * @package AspectMock
 */
class Registry {

    /**
     * @return Mock
     */
    protected static function getMockAspect()
    {
        return Kernel::getInstance()->getContainer()->getAspect('AspectMock\Mock');
    }

    static function registerClass($name, $params)
    {
        self::getMockAspect()->registerClass($name, $params);
    }

    static function registerObject($object, $params)
    {
        self::getMockAspect()->registerObject($object, $params);
    }

    static function clean()
    {
        self::getMockAspect()->clean();
    }

}