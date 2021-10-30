<?php

declare(strict_types=1);

namespace AspectMock\Proxy;

use ReflectionClass;

class AnythingClassProxy extends ClassProxy {

    public function __construct($class_name)
    {
        $this->className = $class_name;
        $this->reflected = new ReflectionClass(Anything::class);
    }

    public function isDefined(): bool
    {
       return false;
    }

    public function construct(): Anything
    {
        return new Anything($this->className);
    }

    public function make(): Anything
    {
        return new Anything($this->className);
    }

    public function interfaces(): array
    {
        return array();
    }

    public function hasMethod($method): bool
    {
        return false;
    }
}
