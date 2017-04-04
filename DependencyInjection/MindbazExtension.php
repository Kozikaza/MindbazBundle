<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MindbazBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MindbazExtension extends Extension
{
    private $classes = [];

    /**
     * Gets the classes to cache.
     *
     * @return array An array of classes
     */
    public function getClassesToCompile()
    {
        return $this->classes;
    }

    /**
     * Adds classes to the class cache.
     *
     * @param array $classes An array of classes
     */
    public function addClassesToCompile(array $classes)
    {
        $this->classes = array_merge($this->classes, $classes);
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('mindbaz.credentials', $config['credentials']);
        $container->setParameter('mindbaz.campaigns', $config['campaigns']);
        $container->setParameter('mindbaz.insertMissingSubscribers', $config['insertMissingSubscribers']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('mailer.xml');

        if (array_key_exists('JMSSerializerBundle', $container->getParameter('kernel.bundles'))) {
            $loader->load('jmsserializer.xml');
        }
    }
}
