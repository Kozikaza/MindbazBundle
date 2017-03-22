<?php

namespace MindbazBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mindbaz');
        $rootNode
            ->children()
                ->scalarNode('wsdl')->defaultValue('http://webservice.mindbaz.com/Campaign.asmx?WSDL')->end()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('api_key')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('site_id')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('login')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end();
        return $treeBuilder;
    }
}