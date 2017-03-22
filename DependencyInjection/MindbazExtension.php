<?php

namespace MindbazBundle\DependencyInjection;

use mbzCampaign\CampaignWebService;
use mbzOneshot\OneshotWebService;
use mbzSubscriber\SubscriberWebService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MindbazExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('mindbaz.api_key', $config['options']['api_key']);
        $container->setParameter('mindbaz.site_id', $config['options']['site_id']);
        $container->setParameter('mindbaz.login', $config['options']['login']);
        $container->setParameter('mindbaz.password', $config['options']['password']);

        $campaignService = new Definition(CampaignWebService::class, [
            $config['options']
        ]);
        $container->setDefinition('mindbaz.campaign.service', $campaignService);

        $oneShotService = new Definition(OneshotWebService::class, [
            $config['options']
        ]);
        $container->setDefinition('mindbaz.oneshot.service', $oneShotService);

        $subscriberService = new Definition(SubscriberWebService::class);
        $container->setDefinition('mindbaz.subscriber.service', $subscriberService);
    }
}