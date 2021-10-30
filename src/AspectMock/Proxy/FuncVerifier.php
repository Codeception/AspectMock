<?php

declare(strict_types=1);

namespace AspectMock\Proxy;

use AspectMock\Core\Registry;

class FuncVerifier extends Verifier
{
    protected $ns;

    public function __construct($namespace)
    {
        $this->ns = $namespace;
    }

    protected function callSyntax($method): string
    {
        return '';
    }

    public function getCallsForMethod($func)
    {
        return Registry::getFuncCallsFor($this->ns . '\\' . $func);
    }
}
