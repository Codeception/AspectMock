<?php

declare(strict_types=1);

namespace AspectMock\Util;

use Closure;

final class ArgumentsFormatter
{
    public static function toString($args): string
    {
        return implode(',',array_map('self::format', $args));
    }

    protected static function format($arg)
    {
        if ($arg instanceof Closure) return 'func()';

        if (is_object($arg)) return '['.get_class($arg).']';

        if (is_array($arg)) return var_export($arg, true);

        if (is_string($arg)) return sprintf("'%s'", $arg);

        if (is_scalar($arg)) return $arg;

        return '*';
    }
}
