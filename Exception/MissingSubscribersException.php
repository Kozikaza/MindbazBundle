<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MindbazBundle\Exception;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MissingSubscribersException extends \LogicException
{
    public function __construct(array $invalid, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf('Missing subscribers in Mindbaz: %s', implode(', ', $invalid)), 0, null);
    }
}
