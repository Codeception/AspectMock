<?php
namespace AspectMock;
use AspectMock\Core\Registry;
use AspectMock\Intercept\BeforeMockTransformer;
use AspectMock\Intercept\ClosureTransformer;
use AspectMock\Intercept\LoadPreachedTransformer;
use Go\Core\AspectContainer;
use Go\Core\AspectKernel;
use Go\Instrument\CleanableMemory;
use Go\Instrument\Transformer\FilterInjectorTransformer;
use Symfony\Component\Finder\Finder;
use Go\Instrument\ClassLoading\SourceTransformingLoader;
use Go\Instrument\Transformer\CachingTransformer;
use Go\Instrument\Transformer\MagicConstantTransformer;
use TokenReflection;

require_once __DIR__.'/Core/Registry.php';

class Kernel extends AspectKernel
{
    public function init(array $options = array())
    {
        if (!isset($options['excludePaths'])) $options['excludePaths'] = [];
        if (!isset($options['debug'])) $options['debug'] = true;
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
     * @param $dir
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
     *
     * @param $file
     */
    public function loadFile($file)
    {
        include FilterInjectorTransformer::rewrite($file);
    }

    protected function registerTransformers()
    {
        $cachePathManager = $this->getContainer()->get('aspect.cache.path.manager');;

        $sourceTransformers = array(
            new FilterInjectorTransformer($this, SourceTransformingLoader::getId(), $cachePathManager),
            new MagicConstantTransformer($this),
            new BeforeMockTransformer(
                $this,
                new TokenReflection\Broker(
                    new CleanableMemory()
                ),
                $this->getContainer()->get('aspect.advice_matcher'),
                $cachePathManager,
                $this->getContainer()->get('aspect.cached.loader')
            )
        );

        return array(
            new CachingTransformer($this, $sourceTransformers, $cachePathManager)
        );
    }
}

require __DIR__ . '/Intercept/before_mock.php';