<?php

namespace spec\MindbazBundle\Exception;

use MindbazBundle\Exception\InvalidCampaignException;
use PhpSpec\ObjectBehavior;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class InvalidCampaignExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InvalidCampaignException::class);
    }
}
