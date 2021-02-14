<?php
namespace AspectMock\Intercept;
use Go\Aop\Aspect;
use Go\Instrument\Transformer\StreamMetaData;
use Go\Instrument\Transformer\WeavingTransformer;
use Go\ParserReflection\ReflectionFile;
use Go\ParserReflection\ReflectionMethod;

class BeforeMockTransformer extends WeavingTransformer
{
    protected $before = " if ((\$__am_res = __amock_before(\$this, __CLASS__, __FUNCTION__, array(%s), false)) !== __AM_CONTINUE__) return \$__am_res; ";
    protected $beforeStatic = " if ((\$__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array(%s), true)) !== __AM_CONTINUE__) return \$__am_res; ";

    public function transform(StreamMetaData $metadata): string
    {
        $result        = self::RESULT_ABSTAIN;
        $reflectedFile = new ReflectionFile($metadata->uri, $metadata->syntaxTree);
        $namespaces    = $reflectedFile->getFileNamespaces();

        foreach ($namespaces as $namespace) {

            $classes = $namespace->getClasses();
            foreach ($classes as $class) {

                // Skip interfaces
                if ($class->isInterface()) {
                    continue;
                }

                // Look for aspects
                if (in_array(Aspect::class, $class->getInterfaceNames())) {
                    continue;
                }

                /** @var ReflectionMethod[] $methods */
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

                    // replace return with yield when method is Generator
                    if ($method->isGenerator()) {
                        $beforeDefinition = str_replace('return', 'yield', $beforeDefinition);
                    }
                    if (method_exists($method, 'getReturnType') && $method->getReturnType() == 'void') {
                        //TODO remove method_exists($method, 'getReturnType') when support for php5 is dropped
                        $beforeDefinition = str_replace('return $__am_res;', 'return;', $beforeDefinition);
                    }

                    $reflectedParams = $method->getParameters();

                    $params = [];

                    foreach ($reflectedParams as $reflectedParam) {
                        $params[] = ($reflectedParam->isPassedByReference() ? '&$' : '$') . $reflectedParam->getName();
                    }
                    $params           = implode(", ", $params);
                    $beforeDefinition = sprintf($beforeDefinition, $params);
                    $tokenPosition    = $method->getNode()->getAttribute('startTokenPos');
                    do {
                        if (($metadata->tokenStream[$tokenPosition][1] ?? '') === '{') {
                            $metadata->tokenStream[$tokenPosition][1] .= $beforeDefinition;
                            $result = self::RESULT_TRANSFORMED;
                            break;
                        }
                        $tokenPosition++;
                    } while (isset($metadata->tokenStream[$tokenPosition]));
                }
            }
        }

        return $result;
    }
}
