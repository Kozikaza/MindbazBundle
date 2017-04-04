<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MindbazBundle\Serializer\Bridge\Visitor;

use JMS\Serializer\GenericSerializationVisitor;
use mbzSubscriber\Subscriber;
use mbzSubscriber\SubscriberFieldData;
use MindbazBundle\Serializer\SubscriberEncoder;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MindbazSerializationVisitor extends GenericSerializationVisitor
{
    /**
     * {@inheritdoc}
     */
    public function getResult()
    {
        $data = $this->getRoot();
        $fld = [];
        foreach ($data as $key => $value) {
            $fld[] = (new SubscriberFieldData(SubscriberEncoder::FIELDS[$key]))->setValue($value);
        }

        $subscriber = new Subscriber(isset($data['id']) ? $data['id'] : -1);
        $subscriber->setFld($fld);

        return $subscriber;
    }
}
