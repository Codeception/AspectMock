<?php
namespace AspectMock\Intercept;
use Go\Instrument\Transformer\StreamMetaData;
use Go\Instrument\Transformer\WeavingTransformer;
use Go\ParserReflection\ReflectionFile;

class BeforeMockTransformer extends WeavingTransformer
{
    protected $before = " if ((\$__am_res = __amock_before(\$this, __CLASS__, __FUNCTION__, array(%s), false)) !== __AM_CONTINUE__) return \$__am_res; ";
    protected $beforeStatic = " if ((\$__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array(%s), true)) !== __AM_CONTINUE__) return \$__am_res; ";

    public function transform(StreamMetaData $metadata)
    {
        $fileName = $metadata->uri;

        $reflectedFile = new ReflectionFile($fileName);
        $namespaces = $reflectedFile->getFileNamespaces();

        $dataArray = explode("\n", $metadata->source);

        foreach ($namespaces as $namespace) {

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
                    if ($method->getDeclaringClass()->name != $class->getName()) {
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

                    // replace return with yield when doccomment shows it returns a Generator
                    if (preg_match('/(\@return\s+[\\\]?Generator)/', $method->getDocComment())) {
                        $beforeDefinition = str_replace('return', 'yield', $beforeDefinition);
                    }
                    $reflectedParams = $method->getParameters();

                    $params = [];

                    foreach ($reflectedParams as $reflectedParam) {
                        $params[] = ($reflectedParam->isPassedByReference() ? '&$' : '$') . $reflectedParam->getName();
                    }
                    $params = implode(", ", $params);
                    $beforeDefinition = sprintf($beforeDefinition, $params);
                    for ($i = $method->getStartLine() - 1; $i < $method->getEndLine(); $i++) {
                        $pos = strpos($dataArray[$i], '{');
                        if ($pos === false) {
                            continue;
                        } else {
                            // Bug FIX for functions that have the curly bracket as default on their own parameters:
                            // Launch a "continue" command if the bracket found have a quote (') or a double quote (")
                            // exactly just before or after
                            if (in_array(substr($dataArray[$i], $pos - 1, 1), ['"', "'"]) ||
                                in_array(substr($dataArray[$i], $pos + 1, 1), ['"', "'"])
                            ) {
                                continue;
                            }
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
