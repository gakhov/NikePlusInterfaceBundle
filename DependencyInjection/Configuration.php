<?php

namespace AG\NikePlusInterfaceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ag_nikeplus_interface')
            ->children()
                ->scalarNode('client_id')
                    ->info('The client ID')
                ->end()
                ->scalarNode('client_secret')
                    ->info('The client API secret')
                ->end()
                ->scalarNode('callback')
                    ->info('The callback URL to pass to Nike+')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
