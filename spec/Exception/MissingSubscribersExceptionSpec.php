<?php

namespace spec\MindbazBundle\Exception;

use MindbazBundle\Exception\MissingSubscribersException;
use PhpSpec\ObjectBehavior;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MissingSubscribersExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['foo@example.com']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MissingSubscribersException::class);
        $this->getMessage()->shouldBeEqualTo('Missing subscribers in Mindbaz: foo@example.com');
    }
}
