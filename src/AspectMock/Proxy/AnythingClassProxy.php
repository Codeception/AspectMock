<?php
namespace AspectMock\Proxy;

class AnythingClassProxy extends ClassProxy {

    public function __construct($class_name)
    {
        $this->className = $class_name;
        $this->reflected = new \ReflectionClass(Anything::class);
    }

    public function isDefined(): bool
    {
       return false;
    }

    public function construct()
    {
        return new Anything($this->className);
    }

    public function make()
    {
        return new Anything($this->className);
    }

    public function interfaces(): array
    {
        return [];
    }

    public function hasMethod($method): bool
    {
        return false;
    }

}
