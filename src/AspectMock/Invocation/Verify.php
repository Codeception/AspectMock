<?php
namespace AspectMock\Invocation;
use AspectMock\Util\ArgumentsFormatter;
use \PHPUnit_Framework_ExpectationFailedException as fail;

trait Verify {

    // too long for not to conflict with other properties.
    protected $____calls = array();

    public function verifyInvoked($name, $params = null)
    {
        $class = get_class($this);
        if (!isset($this->____calls[$name])) throw new fail("Expected $class->$name to be invoked but it never occured.");

        if (is_array($params)) {
            foreach ($this->____calls[$name] as $args) {
                $cut = empty($params) ?
                    $cut = $args :
                    array_slice($args, 0, count($params));
                if ($cut === $params) return;
            }
            $params = ArgumentsFormatter::toString($params);
            throw new fail("Expected $class->$name($params) to be invoked but it never occured.");
        }
    }

    public function verifyInvokedOnce($name, $params = null)
    {
        $this->verifyInvokedMultipleTimes($name, 1, $params);
    }

    public function verifyInvokedMultipleTimes($name, $times, $params = null)
    {
        if ($times == 0) return $this->verifyNeverInvoked($name, $params);
        $class = get_class($this);
        if (!isset($this->____calls[$name])) throw new fail("Expected $class->$name to be invoked $times times but it never occur.");
        if (is_array($params)) {
            $equals = 0;
            foreach ($this->____calls[$name] as $args) {
                $cut = empty($params) ?
                    $cut = [] :
                    array_slice($args, 0, count($params));
                if ($cut === $params) $equals++;
            }
            if ($equals == $times) return;
            $params = ArgumentsFormatter::toString($params);
            throw new fail("Expected $class->$name($params) to be invoked but called $equals.");
        }
        $calls = count($this->____calls[$name]);
        if (count($this->____calls[$name]) != $times) throw new fail("Expected $class->$name to be invoked $times times but called $calls.");

    }

    public function verifyNeverInvoked($name, $params = null)
    {
        $class = get_class($this);
        if (is_array($params)) {
            if (!isset($this->____calls[$name])) return;
            $params = ArgumentsFormatter::toString($params);
            foreach ($this->____calls[$name] as $args) {
                $cut = empty($params) ?
                    $cut = [] :
                    array_slice($args, 0, count($params));
                if ($cut === $params) throw new fail("Expected $class->$name($params) not to be invoked but it was.");;
            }
            return;

        }
        if (isset($this->____calls[$name])) throw new fail("Expected $class->$name not to be invoked but it was.");
    }

}