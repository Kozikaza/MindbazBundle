<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MindbazBundle\Manager;

use mbzOneshot\OneshotWebService;
use mbzOneshot\Send;
use mbzSubscriber\ArrayOfInt;
use mbzSubscriber\ArrayOfString;
use mbzSubscriber\GetSubscribersByEmail;
use mbzSubscriber\InsertSubscriber;
use mbzSubscriber\Subscriber as MindbazSubscriber;
use mbzSubscriber\SubscriberWebService;
use mbzSubscriber\Unsubscribe;
use MindbazBundle\Exception\SendErrorException;
use MindbazBundle\Model\Subscriber;
use MindbazBundle\Serializer\Bridge\Serializer;
use MindbazBundle\Serializer\SubscriberEncoder;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class SubscriberManager
{
    const MINDBAZ_SEND_RESPONSE_OK = 'OK';
    const MINDBAZ_SEND_RESPONSE_NOK = 'NOK';

    /**
     * @var SubscriberWebService
     */
    private $subscriberWebService;

    /**
     * @var OneshotWebService
     */
    private $oneshotWebService;

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
     * @param OneshotWebService    $oneshotWebService
     * @param Serializer           $serializer
     * @param LoggerInterface|null $logger
     */
    public function __construct(SubscriberWebService $subscriberWebService, OneshotWebService $oneshotWebService, Serializer $serializer, LoggerInterface $logger = null)
    {
        $this->subscriberWebService = $subscriberWebService;
        $this->oneshotWebService = $oneshotWebService;
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
                (new ArrayOfString())->setString($emails),
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

    /**
     * @param int                 $idCampaign
     * @param Subscriber          $subscriber
     * @param \Swift_Mime_Message $message
     */
    public function send($idCampaign, Subscriber $subscriber, \Swift_Mime_Message $message)
    {
        $response = $this->oneshotWebService->Send(
            new Send(
                $idCampaign,
                $subscriber->getId(),
                $this->getBody($message, 'text/html'),
                $this->getBody($message, 'text/plain'),
                $message->getSender(),
                $message->getSubject()
            )
        );
        if (self::MINDBAZ_SEND_RESPONSE_OK === $response->getSendResult()) {
            $this->logger->info('Message successfully sent to subscriber', ['id' => $subscriber->getId()]);
        } else {
            $this->logger->error('An error occurred while sending the message to subscriber', ['id' => $subscriber->getId(), 'response' => $response->getSendResult()]);

            throw new SendErrorException();
        }
    }

    /**
     * @param \Swift_Mime_Message $message
     * @param string              $contentTypeRequired
     *
     * @return null|string
     */
    private function getBody(\Swift_Mime_Message $message, $contentTypeRequired)
    {
        $contentType = $message->getContentType();

        if ('multipart/alternative' === $contentType) {
            $contentType = $message->getBody() !== strip_tags($message->getBody()) ? 'text/html' : 'text/plain';
        }

        if ($contentTypeRequired === $contentType) {
            return $message->getBody();
        }

        foreach ($message->getChildren() as $child) {
            if ($contentTypeRequired === $child->getContentType()) {
                return $child->getBody();
            }
        }

        return null;
    }
}
