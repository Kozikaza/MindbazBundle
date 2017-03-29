<?php

namespace spec\MindbazBundle\SwiftMailer;

use MindbazBundle\SwiftMailer\MindbazTransport;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MindbazTransportSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MindbazTransport::class);
    }
}
