<?php
namespace AspectMock;
use Go\Core\AspectContainer;
use Go\Core\AspectKernel;

class Kernel extends AspectKernel
{
    protected function configureAop(AspectContainer $container)
    {
        $container->registerAspect(new Core\Mocker);
    }
}