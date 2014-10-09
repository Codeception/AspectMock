<?php
namespace AspectMock\Intercept;

class FunctionInjector
{
    protected $template = <<<EOF
<?php
namespace {{ns}};
if (!function_exists('{{ns}}\{{func}}')) {
function {{func}}() {
    if ((\$__am_res = __amock_before_func('{{ns}}','{{func}}', func_get_args())) !== __AM_CONTINUE__) {
        return \$__am_res;
    }
    return call_user_func_array('{{func}}', func_get_args());
}
}
EOF;
    protected $namespace;
    protected $function;
    protected $fileName;

    function __construct($namespace, $function)
    {
        $this->namespace = $namespace;
        $this->function = $function;
        $this->place('ns', $this->namespace);
        $this->place('func', $this->function);

    }

    public function save()
    {
        $this->fileName = tempnam(sys_get_temp_dir(), $this->function);
        file_put_contents($this->fileName, $this->template);
    }

    public function inject()
    {
        require_once $this->fileName;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getPHP()
    {
        return $this->template;
    }

    protected function place($var, $value)
    {
        $this->template = str_replace("{{{$var}}}", $value, $this->template);
    }
} 