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

use Kozikaza\MindbazBundle\Model\Subscriber;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class SubscriberNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param Subscriber $subscriber
     * @param string     $format
     * @param array      $context
     *
     * @return array
     */
    public function normalize($subscriber, $format = null, array $context = [])
    {
        $data = [];
        $reflectionClass = new \ReflectionClass(Subscriber::class);
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($subscriber);
            if (null === $value || empty($value)) {
                continue;
            }
            $data[$property->getName()] = $value;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Subscriber;
    }

    /**
     * @param array  $data
     * @param string $class
     * @param string $format
     * @param array  $context
     *
     * @return Subscriber
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $subscriber = new Subscriber();
        $reflectionClass = new \ReflectionClass(Subscriber::class);
        foreach ($data as $key => $value) {
            $property = $reflectionClass->getProperty($key);
            $property->setAccessible(true);
            $property->setValue($subscriber, $value);
        }

        return $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return Subscriber::class === $type;
    }
}
