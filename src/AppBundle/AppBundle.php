<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\AddConsoleCommandPass;
use AppBundle\Guzzle\StackMiddlewareCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new StackMiddlewareCompilerPass());
        $container->addCompilerPass(new AddConsoleCommandPass('nab3a'));
    }
}
