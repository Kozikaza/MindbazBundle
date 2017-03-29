<?php

namespace MindbazBundle\Exception;

use Exception;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MissingSubscribersException extends \LogicException
{
    public function __construct(array $invalid, $code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf('Missing subscribers in Mindbaz: %s', implode(', ', $invalid)), 0, null);
    }
}
