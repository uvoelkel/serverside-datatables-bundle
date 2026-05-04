<?php

namespace Voelkel\DataTablesBundle;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Voelkel\DataTablesBundle\Table\TableInterface;

class VoelkelDataTablesBundle extends Bundle implements CompilerPassInterface
{
    public function build(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(TableInterface::class)
                  ->addTag('serverside_datatable');

        $container->addCompilerPass($this);
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('serverside_datatable') as $id => $tags) {
            $container->setAlias($id . '.public', $id)->setPublic(true);
        }
    }
}
