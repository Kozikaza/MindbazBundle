<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Kozikaza\MindbazBundle\DependencyInjection;

use Kozikaza\MindbazBundle\DependencyInjection\SwiftmailerExtension;
use PhpSpec\ObjectBehavior;
use Symfony\Bundle\SwiftmailerBundle\DependencyInjection\SwiftmailerExtension as BaseSwiftmailerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class SwiftmailerExtensionSpec extends ObjectBehavior
{
    function let(BaseSwiftmailerExtension $extension)
    {
        $this->beConstructedWith($extension);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SwiftmailerExtension::class);
    }

    function it_loads_mindbaz_configuration_through_swiftmailer_configuration(BaseSwiftmailerExtension $extension, ContainerBuilder $container)
    {
        $container->prependExtensionConfig('mindbaz', [
            'credentials' => [
                'idSite'   => 123,
                'login'    => 'foo',
                'password' => 'bar',
            ],
            'campaigns'   => ['foo', 'bar'],
        ])->shouldBeCalledTimes(1);
        $extension->load([
            [
                'mailers' => [
                    'foo'     => [],
                    'direct'  => [
                        'transport' => 'direct',
                    ],
                ],
            ],
            [
                'mailers' => [
                    'bar'     => [
                        'transport' => 'mindbaz',
                    ],
                    'mindbaz' => [
                        'transport' => 'mindbaz',
                    ],
                ],
            ]
        ], $container)->shouldBeCalledTimes(1);
        $this->load([
            [
                'mailers' => [
                    'foo'     => [],
                    'direct'  => [
                        'transport' => 'direct',
                    ],
                ],
            ],
            [
                'mailers' => [
                    'bar'     => [
                        'transport' => 'mindbaz',
                    ],
                    'mindbaz' => [
                        'transport' => 'mindbaz',
                        'id_site'   => 123,
                        'username'  => 'foo',
                        'password'  => 'bar',
                        'campaigns' => ['foo', 'bar'],
                    ],
                ],
            ],
        ], $container);
    }
}
