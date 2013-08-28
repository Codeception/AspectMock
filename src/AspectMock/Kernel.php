<?php
namespace AspectMock;
use Go\Core\AspectContainer;
use Go\Core\AspectKernel;
use Go\Instrument\Transformer\FilterInjectorTransformer;
use Symfony\Component\Finder\Finder;

class Kernel extends AspectKernel
{
    public function init(array $options = array())
    {
        if (!isset($options['excludePaths'])) $options['excludePaths'] = [];
        $options['excludePaths'][] = __DIR__;
        parent::init($options);
    }

    protected function configureAop(AspectContainer $container)
    {
        ini_set('xdebug.max_nesting_level', 500);
        $container->registerAspect(new Core\Mocker);
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
}