<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\MindbazBundle\Exception;

use MindbazBundle\Exception\MissingSubscribersException;
use PhpSpec\ObjectBehavior;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MissingSubscribersExceptionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['foo@example.com']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MissingSubscribersException::class);
        $this->getMessage()->shouldBeEqualTo('Missing subscribers in Mindbaz: foo@example.com');
    }
}
