<?php

namespace Voelkel\DataTablesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @codeCoverageIgnore
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('voelkel_data_tables', 'array');

        $rootNode
            ->children()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('theme')
                            ->defaultValue('default')
                        ->end()
                        ->arrayNode('state')
                            ->children()
                                ->scalarNode('save')->defaultFalse()->end()
                                ->scalarNode('duration')->defaultValue(7200)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('table_options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('dom')->end()
                    ->end()
                ->end()
                ->arrayNode('localization')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('locale')->end()
                        ->arrayNode('data')
                            ->children()
                                ->scalarNode('true')->end()
                                ->scalarNode('false')->end()
                                ->scalarNode('datetime')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
