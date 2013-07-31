<?php
namespace AspectMock\Proxy;
use AspectMock\Core\Registry;
use PHPUnit_Framework_Assert as a;

class MethodProxy {
    
    protected $values = [];
    protected $signature;

    public function __construct($classOrInstance, $method)
    {
        $this->values = Registry::getReturnedValues($classOrInstance, $method);
        $class = is_object($classOrInstance)
            ? get_class($classOrInstance)
            : $classOrInstance;
        $this->signature = "$class->$method()";
    }

    public function returned($result)
    {
        a::assertContains($result, $this->values, "{$this->signature} returned the expected result at least once");
    }

    public function returnedOnce($result)
    {
        $this->returnedMultipleTimes($result, 1);
    }

    public function returnedMultipleTimes($result, $times)
    {
        $values = array_count_values($this->values);
        if (!isset($values[$result])) a::fail("{$this->signature} returned the expected result at least once");

        a::assertEquals($times, $values[$result], "{$this->signature} returned the expected result $times times");
    }

    public function neverReturned($result)
    {
        a::assertNotContains($result, $this->values, "{$this->signature} never returned the expected result");
    }

    public function results()
    {
        return $this->values;
    }

    public function result()
    {

        return end($this->values);
    }

}