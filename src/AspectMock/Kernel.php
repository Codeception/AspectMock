<?php

declare(strict_types=1);

namespace AspectMock;

use AspectMock\Core\Registry;
use AspectMock\Intercept\BeforeMockTransformer;
use Go\Core\AspectContainer;
use Go\Core\AspectKernel;
use Go\Core\AdviceMatcher;
use Go\Core\CachedAspectLoader;
use Go\Instrument\ClassLoading\CachePathManager;
use Go\Instrument\ClassLoading\SourceTransformingLoader;
use Go\Instrument\Transformer\CachingTransformer;
use Go\Instrument\Transformer\FilterInjectorTransformer;
use Go\Instrument\Transformer\MagicConstantTransformer;
use Symfony\Component\Finder\Finder;

require_once __DIR__ . '/Core/Registry.php';

class Kernel extends AspectKernel
{
    public function init(array $options = []): void
    {
        if (!isset($options['excludePaths'])) {
            $options['excludePaths'] = [];
        } elseif (!is_array($options['excludePaths'])) {
            $options['excludePaths'] = [ $options['excludePaths'] ];
        }
        $options['debug'] = true;
        $options['excludePaths'][] = __DIR__;

        parent::init($options);
    }

    protected function configureAop(AspectContainer $container)
    {
        Registry::setMocker(new Core\Mocker);
    }

    /**
     * Scans a directory provided and includes all PHP files from it.
     * All files will be parsed and aspects will be added.
     *
     * @param string|string[] $dir
     */
    public function loadPhpFiles($dir)
    {
        $files = Finder::create()->files()->name('*.php')->in($dir);
        foreach ($files as $file) {
            $this->loadFile($file->getRealpath());
        }

    }

    /**
     * Includes file and injects aspect pointcuts into int
     */
    public function loadFile(string $file)
    {
        include FilterInjectorTransformer::rewrite($file);
    }

    protected function registerTransformers(): array
    {
        $cachePathManager = $this->getContainer()->getService(CachePathManager::class);

        $sourceTransformers = [
            new FilterInjectorTransformer($this, SourceTransformingLoader::getId(), $cachePathManager),
            new MagicConstantTransformer($this),
            new BeforeMockTransformer(
                $this,
                $this->getContainer()->getService(AdviceMatcher::class),
                $cachePathManager,
                $this->getContainer()->getService(CachedAspectLoader::class)
            )
        ];

        return [
            new CachingTransformer($this, $sourceTransformers, $cachePathManager)
        ];
    }
}

require __DIR__ . '/Intercept/before_mock.php';
