<?php
namespace AspectMock\Core;
use AspectMock\Util\ArgumentsFormatter;
use \PHPUnit_Framework_ExpectationFailedException as fail;

class ClassProxy implements \AspectMock\Invocation\Verifiable {

    protected $class_name;

    public function __construct($class_name)
    {
        $this->class_name = $class_name;
    }

    protected function getCallsForMethod($method)
    {
        $calls = Registry::getClassCallsFor($this->class_name);
        return isset($calls[$method])
            ? $calls[$method]
            : [];
    }

    protected function callSyntax($method)
    {
        return method_exists($this->class_name,$method)
            ? '::'
            : '->';
    }

    public function verifyInvoked($name, $params = null)
    {
        $calls = $this->getCallsForMethod($name);
        $separator = $this->callSyntax($name);

        if (empty($calls)) throw new fail("Expected {$this->class_name}$separator$name to be invoked but it never occur.");
        if (is_array($params)) {
            foreach ($calls as $args) {
                $cut = empty($params) ?
                    $cut = $args :
                    array_slice($args, 0, count($params));
                if ($cut == $params) return;
            }
            $params = ArgumentsFormatter::toString($params);
            throw new fail("Expected {$this->class_name}$separator$name($params) to be invoked but it never occur.");
        }
    }

    public function verifyInvokedOnce($name, $params = null)
    {
        $this->verifyInvokedMultipleTimes($name, 1, $params);
    }

    public function verifyInvokedMultipleTimes($name, $times, $params = null)
    {
        if ($times == 0) return $this->verifyNeverInvoked($name, $params);

        $calls = $this->getCallsForMethod($name);
        $separator = $this->callSyntax($name);

        if (empty($calls)) throw new fail("Expected {$this->class_name}$separator$name to be invoked $times times but it never occur.");
        if (is_array($params)) {
            $equals = 0;
            foreach ($calls as $args) {
                $cut = empty($params) ?
                    $cut = $args :
                    array_slice($args, 0, count($params));
                if ($cut == $params) $equals++;
            }
            if ($equals == $times) return;
            $params = ArgumentsFormatter::toString($params);
            throw new fail("Expected {$this->class_name}$separator$name($params) to be invoked but called $equals.");
        }
        $num_calls = count($calls);
        if ($num_calls != $times) throw new fail("Expected {$this->class_name}$separator$name to be invoked $times times but called $num_calls.");

    }

    public function verifyNeverInvoked($name, $params = null)
    {
        $calls = $this->getCallsForMethod($name);
        $separator = $this->callSyntax($name);

         if (is_array($params)) {
             if (empty($calls)) return;
             $params = ArgumentsFormatter::toString($params);
             foreach ($calls as $args) {
                 $cut = empty($params) ?
                     $cut = $args :
                    array_slice($args, 0, count($params));
                 if ($cut == $params) throw new fail("Expected {$this->class_name}$separator$name($params) not to be invoked but it was.");;
             }
             return;
         }
         if (count($calls)) throw new fail("Expected {$this->class_name}$separator$name not to be invoked but it was.");
    }
}