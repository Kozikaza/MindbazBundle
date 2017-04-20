<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kozikaza\MindbazBundle\Serializer\Bridge\Visitor;

use JMS\Serializer\GenericDeserializationVisitor;
use Kozikaza\MindbazBundle\Serializer\SubscriberEncoder;
use mbzSubscriber\Subscriber;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MindbazDeserializationVisitor extends GenericDeserializationVisitor
{
    /**
     * @param Subscriber $subscriber
     *
     * @return array
     */
    protected function decode($subscriber)
    {
        $fields = array_flip(SubscriberEncoder::FIELDS);
        $data = [];
        foreach ($subscriber->getFld() as $fld) {
            $data[$fields[$fld->getIdField()]] = $fld->getValue();
        }

        return $data;
    }
}
