<?php

declare(strict_types=1);

namespace AspectMock\Intercept;

use Exception;
use ReflectionFunction;

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

    protected $templateByRefOptional = <<<EOF
<?php
namespace {{ns}};
if (!function_exists('{{ns}}\{{func}}')) {
    function {{func}}({{arguments}}) {
         \$args = [];
         switch(count(func_get_args())) {
{{code}}         }
         if ((\$__am_res = __amock_before_func('{{ns}}','{{func}}', \$args)) !== __AM_CONTINUE__) {
             return \$__am_res;
         }
         return call_user_func_array('{{func}}', \$args);
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
        $this->placeOptionalAndReferenceFunction($namespace, $function);
        $this->place('ns', $this->namespace);
        $this->place('func', $this->function);
    }

    public function getParameterDeclaration(\ReflectionParameter $parameter, $internal)
    {
        $text = (string)$parameter;
        if (preg_match('#Parameter\s\#\d+\s\[\s<(required|optional)>(.*)(\sor NULL)(.*)\s]#', $text, $match)) {
            $text = $match[2].$match[4];
        } elseif (preg_match('#Parameter\s\#\d+\s\[\s<(required|optional)>\s(.*)\s]#', $text, $match)) {
            $text = $match[2];
        } else {
            throw new Exception('reflection api changed. adjust code.');
        }

        if ($internal && $parameter->isOptional()) {
            $text .= "=NULL";
        }

        return $text;
    }

    public function placeOptionalAndReferenceFunction($namespace, $function): void
    {
        $reflect = new ReflectionFunction($function);
        $parameters = [];
        $args = '';
        $byRef = false;
        $optionals = false;
        $names = [];
        $internal = $reflect->isInternal();
        foreach ($reflect->getParameters() as $parameter) {
            $name = '$'.$parameter->getName();
            $newname = '$p'.$parameter->getPosition();
            $declaration = str_replace($name, $newname, $this->getParameterDeclaration($parameter, $internal));
            $name = $newname;
            if (!$optionals && $parameter->isOptional()) {
                $optionals = true;
            }

            if ($parameter->isPassedByReference()) {
                $name = '&'.$name;
                $byRef = true;
            }

            $names[] = $name;
            $parameters[$newname] = $declaration;
        }

        if ($byRef) {
            $this->template = $this->templateByRefOptional;
            $this->place('arguments', implode(', ', $parameters));
            $code = '';
            for ($i = count($parameters); $i > 0; --$i) {
                $code .= sprintf('             case %d: $args = [', $i) . implode(', ', $names) . "]; break;\n";
                array_pop($names);
            }

            $this->place('code', $code);
        }
    }

    public function save(): void
    {
        $this->fileName = tempnam(sys_get_temp_dir(), $this->function);
        file_put_contents($this->fileName, $this->template);
    }

    public function inject(): void
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

    protected function place($var, $value): void
    {
        $this->template = str_replace(sprintf('{{%s}}', $var), $value, $this->template);
    }
} 
