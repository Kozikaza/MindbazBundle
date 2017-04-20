<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kozikaza\MindbazBundle\Manager;

use Kozikaza\MindbazBundle\Model\Subscriber;
use Kozikaza\MindbazBundle\Serializer\Bridge\Serializer;
use Kozikaza\MindbazBundle\Serializer\SubscriberEncoder;
use mbzSubscriber\ArrayOfInt;
use mbzSubscriber\ArrayOfString;
use mbzSubscriber\GetSubscribersByEmail;
use mbzSubscriber\InsertSubscriber;
use mbzSubscriber\Subscriber as MindbazSubscriber;
use mbzSubscriber\SubscriberWebService;
use mbzSubscriber\Unsubscribe;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class SubscriberManager
{
    /**
     * @var SubscriberWebService
     */
    private $subscriberWebService;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SubscriberWebService $subscriberWebService
     * @param Serializer           $serializer
     * @param LoggerInterface|null $logger
     */
    public function __construct(SubscriberWebService $subscriberWebService, Serializer $serializer, LoggerInterface $logger = null)
    {
        $this->subscriberWebService = $subscriberWebService;
        $this->serializer = $serializer;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param array $fields
     *
     * @return Subscriber
     */
    public function create(array $fields)
    {
        $subscriber = $this->serializer->denormalize($fields, Subscriber::class);
        $this->insert($subscriber);

        return $subscriber;
    }

    /**
     * @param Subscriber $subscriber
     */
    public function insert(Subscriber $subscriber)
    {
        /** @var MindbazSubscriber $mbzSubscriber */
        $mbzSubscriber = $this->serializer->serialize($subscriber, SubscriberEncoder::FORMAT);
        $result = $this->subscriberWebService->InsertSubscriber(new InsertSubscriber($mbzSubscriber, true));
        $subscriber->setId($result->getInsertSubscriberResult());

        $this->logger->info('New subscriber inserted in Mindbaz', ['id' => $subscriber->getId()]);
    }

    /**
     * @param Subscriber $subscriber
     */
    public function unsubscribe(Subscriber $subscriber)
    {
        $result = $this->subscriberWebService->Unsubscribe(new Unsubscribe($subscriber->getId(), null, null));
        if (true === $result->getUnsubscribeResult()) {
            $this->logger->info('Subscriber successfully unsubscribed', ['id' => $subscriber->getId()]);
        } else {
            $this->logger->error('An error occurred while unsubscribing subscriber', ['id' => $subscriber->getId(), 'response' => $result->getUnsubscribeResult()]);
        }
    }

    /**
     * @param array $emails
     *
     * @return array
     */
    public function findByEmail(array $emails)
    {
        $result = $this->subscriberWebService->GetSubscribersByEmail(
            new GetSubscribersByEmail(
                (new ArrayOfString())->setString(array_map('strtolower', $emails)),
                (new ArrayOfInt())->setInt([0, 1])
            )
        )->getGetSubscribersByEmailResult();

        // Unable to find related subscribers
        if (null === $result) {
            return [];
        }

        $subscribers = [];
        foreach ($result->getSubscriber() as $subscriber) {
            $subscribers[] = $this->serializer->deserialize($subscriber, Subscriber::class, SubscriberEncoder::FORMAT);
        }

        return $subscribers;
    }

    /**
     * @param string $email
     *
     * @return Subscriber|null
     */
    public function findOneByEmail($email)
    {
        $subscribers = $this->findByEmail([$email]);

        return 0 < count($subscribers) ? $subscribers[0] : null;
    }
}
