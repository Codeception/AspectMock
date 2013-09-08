<?php
namespace AspectMock\Intercept;
use Go\Instrument\Transformer\StreamMetaData;
use Go\Instrument\Transformer\WeavingTransformer;
use TokenReflection\Exception\FileProcessingException;
use TokenReflection\Broker;
use TokenReflection\Php\ReflectionMethod;
use TokenReflection\Php\ReflectionParameter;
use TokenReflection\ReflectionClass as ParsedClass;
use TokenReflection\ReflectionFileNamespace as ParsedFileNamespace;

class ClosureTransformer extends WeavingTransformer {

    protected $before = " return __amock_around(\$this, __CLASS__, __FUNCTION__, array(%s), false, function(%s)";
    protected $beforeStatic = " return __amock_around(get_called_class(), __CLASS__, __FUNCTION__, array(%s), true, function(%s)";
    protected $after = ");}";

    public function transform(StreamMetaData $metadata)
    {
        $fileName = $metadata->uri;
        if ($this->includePaths) {
            $found = false;
            foreach ($this->includePaths as $includePath) {
                if (strpos($fileName, $includePath) === 0) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return;
            }
        }

        foreach ($this->excludePaths as $excludePath) {
            if (strpos($fileName, $excludePath) === 0) {
                return;
            }
        }

        try {
            $parsedSource = $this->broker->processString($metadata->source, $fileName, true);
        } catch (FileProcessingException $e) {
            throw new \RuntimeException("AspectMock couldn't parse some of files.\n Try to exclude them from parsing list.\n" . $e->getDetail());
        }

        /** @var $namespaces ParsedFileNamespace[] */
        $namespaces = $parsedSource->getNamespaces();
        $dataArray = explode("\n", $metadata->source);

        foreach ($namespaces as $namespace) {

            /** @var $classes ParsedClass[] */
            $classes = $namespace->getClasses();
            foreach ($classes as $class) {

                // Skip interfaces
                if ($class->isInterface()) {
                    continue;
                }

                // Look for aspects
                if (in_array('Go\Aop\Aspect', $class->getInterfaceNames())) {
                    continue;
                }

                $methods = $class->getMethods();
                foreach ($methods as $method) {
                    /** @var $method ReflectionMethod`  **/
                    if ($method->getDeclaringClassName() != $class->getName()) continue;
                    if ($method->isAbstract()) continue;
                     $aroundDefinition = $method->isStatic()
                        ? $this->beforeStatic
                        : $this->before;
                    $reflectedParams = $method->getParameters();

                    $params = [];

                    foreach ($reflectedParams as $reflectedParam) {
                        /** @var $reflectedParam ReflectionParameter  **/
                        $params[] = '$'.$reflectedParam->getName();
                    }
                    $params = implode(", ", $params);
                    $inject = sprintf($aroundDefinition, $params, $params);
                    for ($i = $method->getStartLine()-1; $i < $method->getEndLine()-1; $i++) {
                        $pos = strpos($dataArray[$i],'{');
                        if ($pos === null) continue;
                        $dataArray[$i] = substr($dataArray[$i], 0, $pos).$inject.' { '.substr($dataArray[$i], $pos);
                        break;
                    }

                    $dataArray[$method->getEndLine()-1] .= $this->after;
                }
            }
        }
        $metadata->source = implode("\n", $dataArray);
    }
}