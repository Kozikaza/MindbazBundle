<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MindbazBundle\Serializer\Bridge;

use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface as JMSSerializerInterface;
use mbzSubscriber\Subscriber as MindbazSubscriber;
use MindbazBundle\Model\Subscriber;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class Serializer
{
    /**
     * @var SerializerInterface|DenormalizerInterface|JMSSerializerInterface|ArrayTransformerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface|DenormalizerInterface|JMSSerializerInterface|ArrayTransformerInterface $serializer
     */
    public function __construct($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array  $data
     * @param string $class
     *
     * @return Subscriber
     */
    public function denormalize(array $data, $class)
    {
        return $this->serializer instanceof JMSSerializerInterface ? $this->serializer->fromArray($data, $class) : $this->serializer->denormalize($data, $class);
    }

    /**
     * @param Subscriber $data
     * @param string     $format
     *
     * @return MindbazSubscriber
     */
    public function serialize(Subscriber $data, $format)
    {
        return $this->serializer->serialize($data, $format);
    }

    /**
     * @param MindbazSubscriber $data
     * @param string            $type
     * @param string            $format
     *
     * @return Subscriber
     */
    public function deserialize(MindbazSubscriber $data, $type, $format)
    {
        return $this->serializer->deserialize($data, $type, $format);
    }
}
