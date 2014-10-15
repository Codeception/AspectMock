<?php
namespace AspectMock\Proxy;

use AspectMock\Core\Registry;

class FuncVerifier extends Verifier
{
    protected $ns;

    public function __construct($namespace)
    {
        $this->ns = $namespace;
    }

    protected function callSyntax($method)
    {
        return "";
    }

    public function getCallsForMethod($func)
    {
        $calls = Registry::getFuncCallsFor($this->ns . '\\' . $func);
        return $calls;
    }


} 