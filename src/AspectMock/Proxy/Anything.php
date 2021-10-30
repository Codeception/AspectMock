<?php

declare(strict_types=1);

namespace AspectMock\Proxy;

use ArrayAccess;
use AspectMock\Util\Undefined;
use Iterator;

/**
 * A class to mimic any other class in PHP.
 *
 * Originally written from Codeception.
 *
 */
class Anything implements Undefined, ArrayAccess, Iterator
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

    function __get($key): Anything
    {
        return new Anything($this->className);
    }

    function __set($key, $val)
    {
    }

    function __call($method, $args): Anything
    {
        return new Anything($this->className);
    }

    public function offsetExists($offset): bool
    {
        return false;
    }

    public function offsetGet($offset): Anything
    {
        return new Anything($this->className);
    }


    public function offsetSet($offset, $value): void
    {
    }

    public function offsetUnset($offset): void
    {
    }

    public function current()
    {
        return null;
    }

    public function next(): void
    {
    }

    public function key()
    {
        return null;
    }

    public function valid(): bool
    {
        return false;
    }

    public function rewind(): void
    {
    }
}
