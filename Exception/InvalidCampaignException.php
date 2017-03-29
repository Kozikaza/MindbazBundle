<?php

namespace MindbazBundle\Exception;

use Exception;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class InvalidCampaignException extends \LogicException
{
    public function __construct($message = 'Invalid Mindbaz campaign', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
