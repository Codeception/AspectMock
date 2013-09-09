<?php
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('AspectMock', __DIR__ . '/../src');
$loader->register();


function clean_doc($doc, $indent = 3)
{
    $lines = explode("\n", $doc);
    $lines = array_map(function ($line) use ($indent)
    {
        return substr($line, $indent);
    }, $lines);
    $doc = implode("\n", $lines);
    $doc = str_replace(array('@since'), array(' * available since version'), $doc);
    $doc = str_replace(array(' @', "\n@"), array("  * ", "\n * "), $doc);
    return $doc;
}

$root = __DIR__.'/../src/AspectMock';
$files = [
    'Test Double Builder' => 'AspectMock\Test',
    'ClassProxy' => 'AspectMock\Proxy\ClassProxy',
    'InstanceProxy' => 'AspectMock\Proxy\InstanceProxy',
];

foreach ($files as $className) {

    $fileName = end(explode('\\', $className));
    $text = '# ' . $className . "\n";

    $class = new ReflectionClass($className);

    $doc = $class->getDocComment();
    if ($doc) $text .= clean_doc($doc, 3);

    $reference = array();
    foreach ($class->getMethods() as $method) {
        if ($method->isConstructor() or $method->isDestructor()) continue;
        if (strpos($method->name,'__')===0) continue;
        if ($method->isPublic()) {
            if ($method->isStatic()) {
                $title = "\n## ".$class->getShortName()."::" . $method->name . "\n\n";
            } else {
                $title = "\n## ->" . $method->name . "\n\n";
            }

            $doc = $method->getDocComment();
            $doc = $doc ? clean_doc($doc, 7) : "__not documented__\n";
            $reference[$method->name] = $title . $doc;
        }

    }
    ksort($reference);
    $text .= implode("\n", $reference);

    file_put_contents(__DIR__ . '/../docs/' . $fileName . '.md', $text);

}