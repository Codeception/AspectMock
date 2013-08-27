<?php

$loader = require(__DIR__.'/../vendor/autoload.php');
$loader->add('AspectMock', __DIR__ . '/../src');
$loader->add('demo', __DIR__ . '/_data');
$loader->register();

$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'cacheDir' => __DIR__.'/_data/cache',
    'includePaths' => [__DIR__.'/_data/demo'],
    'interceptFunctions' => true
]);