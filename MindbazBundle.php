<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kozikaza\MindbazBundle;

use Kozikaza\MindbazBundle\DependencyInjection\SwiftmailerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MindbazBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        if ($container->hasExtension('swiftmailer')) {
            $container->registerExtension(new SwiftmailerExtension($container->getExtension('swiftmailer')));
        }
    }
}
