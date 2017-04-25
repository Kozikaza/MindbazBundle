<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kozikaza\MindbazBundle\DependencyInjection;

use Symfony\Bundle\SwiftmailerBundle\DependencyInjection\SwiftmailerExtension as BaseSwiftmailerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Swiftmailer bridge: declare Mindbaz configuration through Swiftmailer configuration.
 *
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class SwiftmailerExtension extends Extension
{
    /**
     * @var ExtensionInterface|BaseSwiftmailerExtension
     */
    protected $extension;

    /**
     * @param ExtensionInterface|BaseSwiftmailerExtension $extension
     */
    public function __construct($extension)
    {
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return $this->extension->getAlias();
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return $this->extension->getNamespace();
    }

    /**
     * @return bool|string
     */
    public function getXsdValidationBasePath()
    {
        return $this->extension->getXsdValidationBasePath();
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        foreach ($configs as $key => $config) {
            if (!isset($config['mailers'])) {
                continue;
            }
            foreach ($config['mailers'] as $name => $mailer) {
                // It's not a Mindbaz transport
                if (!isset($mailer['transport']) || 'mindbaz' !== $mailer['transport']) {
                    continue;
                }
                // Mindbaz parameters are not set through Swiftmailer configuration
                if (!array_key_exists('id_site', $mailer) ||
                    !array_key_exists('username', $mailer) ||
                    !array_key_exists('password', $mailer) ||
                    !array_key_exists('campaigns', $mailer)
                ) {
                    continue;
                }
                // Set/erase Mindbaz configuration
                $container->prependExtensionConfig('mindbaz', [
                    'credentials' => [
                        'idSite'   => $mailer['id_site'],
                        'login'    => $mailer['username'],
                        'password' => $mailer['password'],
                    ],
                    'campaigns'   => $mailer['campaigns'],
                ]);
                unset(
                    $configs[$key]['mailers'][$name]['id_site'],
                    $configs[$key]['mailers'][$name]['username'],
                    $configs[$key]['mailers'][$name]['password'],
                    $configs[$key]['mailers'][$name]['campaigns']
                );
            }
        }
        $this->extension->load($configs, $container);
    }
}
