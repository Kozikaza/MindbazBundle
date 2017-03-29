<?php

namespace spec\MindbazBundle\DependencyInjection;

use MindbazBundle\DependencyInjection\MindbazExtension;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MindbazExtensionSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(MindbazExtension::class);
    }

    public function it_get_classes_to_compile()
    {
        $this->addClassesToCompile(['foo', 'bar']);
        $this->getClassesToCompile()->shouldBeEqualTo(['foo', 'bar']);
    }

    public function it_loads(ContainerBuilder $container)
    {
        $container->setParameter('mindbaz.credentials', [
            'idSite'   => 1234,
            'login'    => 'foo',
            'password' => 'bar',
        ]);
        $this->load(
            [
                [
                    'credentials'              => [
                        'idSite'   => 1234,
                        'login'    => 'foo',
                        'password' => 'bar',
                    ],
                    'campaigns'                => [
                        'foo' => 12,
                    ],
                    'insertMissingSubscribers' => true,
                ],
            ],
            $container
        );
    }
}
