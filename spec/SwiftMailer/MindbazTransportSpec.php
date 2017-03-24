<?php

namespace spec\MindbazBundle\SwiftMailer;

use MindbazBundle\SwiftMailer\MindbazTransport;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MindbazTransportSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MindbazTransport::class);
    }
}
