<?php
namespace AspectMock\Intercept;
use AspectMock\Core\Registry;
use Go\Instrument\Transformer\StreamMetaData;
use Go\Instrument\Transformer\WeavingTransformer;
use TokenReflection\Exception\FileProcessingException;
use TokenReflection\Broker;
use TokenReflection\Php\ReflectionMethod;
use TokenReflection\Php\ReflectionParameter;
use TokenReflection\ReflectionClass as ParsedClass;
use TokenReflection\ReflectionFileNamespace as ParsedFileNamespace;

class BeforeMockTransformer extends WeavingTransformer
{
    protected $before = " if ((\$__am_res = __amock_before(\$this, __CLASS__, __FUNCTION__, array(%s), false)) !== __AM_CONTINUE__) return \$__am_res; ";
    protected $beforeStatic = " if ((\$__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array(%s), true)) !== __AM_CONTINUE__) return \$__am_res; ";

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
                    /** @var $method ReflectionMethod`  * */
                    if ($method->getDeclaringClassName() != $class->getName()) {
                        continue;
                    }
                    // methods from traits have the same declaring class name, so check that the filenames match, too
                    if ($method->getFileName() != $class->getFileName()) {
                        continue;
                    }
                    if ($method->isAbstract()) {
                        continue;
                    }
                    $beforeDefinition = $method->isStatic()
                        ? $this->beforeStatic
                        : $this->before;
                    $reflectedParams = $method->getParameters();

                    $params = [];

                    foreach ($reflectedParams as $reflectedParam) {
                        /** @var $reflectedParam ReflectionParameter  * */
                        $params[] = ($reflectedParam->isPassedByReference() ? '&$' : '$') . $reflectedParam->getName();
                    }
                    $params = implode(", ", $params);
                    $beforeDefinition = sprintf($beforeDefinition, $params);
                    for ($i = $method->getStartLine() - 1; $i < $method->getEndLine() - 1; $i++) {
                        $pos = strpos($dataArray[$i], '{');
                        if ($pos === false) {
                            continue;
                        }
                        $dataArray[$i] = substr($dataArray[$i], 0, $pos + 1) . $beforeDefinition . substr($dataArray[$i], $pos + 1);
                        break;
                    }
                }
            }
        }
        $metadata->source = implode("\n", $dataArray);
    }

}