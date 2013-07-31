<?php
namespace AspectMock\Proxy;

use AspectMock\Util\Undefined;
/**
 * A class to mimic any other class in PHP.
 *
 * Originally written from Codeception.
 *
 */
class Anything implements Undefined, \ArrayAccess, \Iterator
{
    private $className;

    public function __construct($className = null)
    {
        $this->className = $className;             
    }

    function __toString()
    {
        return "| Undefined | ".$this->className;
    }

    function __get($key)
    {
        return new Anything($this->className);
    }

    function __set($key, $val)
    {
    }

    function __call($method, $args)
    {
        return new Anything($this->className);
    }

    public function offsetExists($offset)
    {
        return false;
    }

    public function offsetGet($offset)
    {
        return new Anything($this->className);
    }


    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }

    public function current()
    {
        return null;
    }

    public function next()
    {
    }

    public function key()
    {
        return null;
    }

    public function valid()
    {
        return false;
    }

    public function rewind()
    {
    }

}
