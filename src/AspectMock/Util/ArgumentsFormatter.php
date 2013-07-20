<?php
namespace AspectMock\Util;

class ArgumentsFormatter
{

    static function toString($args)
    {
        return implode(',',array_map('self::format', $args));
    }

    protected static function format($arg)
    {
        if ($arg instanceof \Closure) return "func()";
        if (is_object($arg)) return '['.get_class($arg).']';
        if (is_array($arg)) return var_export($arg);
        if (is_string($arg)) return "'$arg'";
        if (is_scalar($arg)) return $arg;
        return "*";
    }

}