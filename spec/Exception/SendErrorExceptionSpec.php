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

use MindbazBundle\Exception\SendErrorException;
use PhpSpec\ObjectBehavior;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class SendErrorExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SendErrorException::class);
    }
}
