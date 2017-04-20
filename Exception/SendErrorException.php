<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kozikaza\MindbazBundle\Exception;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class SendErrorException extends \RuntimeException
{
    public function __construct($message = 'An error occurred while sending the message', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
