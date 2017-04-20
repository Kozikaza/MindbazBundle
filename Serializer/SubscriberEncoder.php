<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kozikaza\MindbazBundle\Serializer;

use mbzSubscriber\Subscriber;
use mbzSubscriber\SubscriberFieldData;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class SubscriberEncoder implements EncoderInterface, DecoderInterface
{
    const FORMAT = 'mindbaz';
    const FIELDS = [
        'id'                    => 0,
        'email'                 => 1,
        'firstSubscriptionDate' => 2,
        'lastSubscriptionDate'  => 3,
        'unsubscriptionDate'    => 4,
        'status'                => 7,
        'civility'              => 13,
        'lastName'              => 14,
        'firstName'             => 15,
        'city'                  => 17,
        'zicode'                => 18,
        'country'               => 19,
    ];

    /**
     * @param array  $data
     * @param string $format
     * @param array  $context
     *
     * @return Subscriber
     */
    public function encode($data, $format, array $context = [])
    {
        $fld = [];
        foreach ($data as $key => $value) {
            $fld[] = (new SubscriberFieldData(self::FIELDS[$key]))->setValue($value);
        }

        $subscriber = new Subscriber(isset($data['id']) ? $data['id'] : -1);
        $subscriber->setFld($fld);

        return $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return self::FORMAT === $format;
    }

    /**
     * @param Subscriber $subscriber
     * @param string     $format
     * @param array      $context
     *
     * @return array
     */
    public function decode($subscriber, $format, array $context = [])
    {
        $fields = array_flip(SubscriberEncoder::FIELDS);
        $data = [];
        foreach ($subscriber->getFld() as $fld) {
            $data[$fields[$fld->getIdField()]] = $fld->getValue();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return self::FORMAT === $format;
    }
}
