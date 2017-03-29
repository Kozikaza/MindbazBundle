<?php

namespace MindbazBundle\SwiftMailer;

use MindbazBundle\Exception\InvalidCampaignException;
use MindbazBundle\Exception\MissingSubscribersException;
use MindbazBundle\Manager\SubscriberManager;
use MindbazBundle\Model\Subscriber;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MindbazTransport implements \Swift_Transport
{
    /**
     * @var SubscriberManager
     */
    private $subscriberManager;

    /**
     * @var \Swift_Events_EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $campaigns;

    /**
     * @var bool
     */
    private $insertMissingSubscribers = false;

    /**
     * @var string|null
     */
    private $campaign;

    /**
     * @param SubscriberManager             $subscriberManager
     * @param \Swift_Events_EventDispatcher $eventDispatcher
     * @param array                         $campaigns
     * @param bool                          $insertMissingSubscribers
     */
    public function __construct(SubscriberManager $subscriberManager, \Swift_Events_EventDispatcher $eventDispatcher, array $campaigns, $insertMissingSubscribers)
    {
        $this->subscriberManager = $subscriberManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->campaigns = $campaigns;
        $this->insertMissingSubscribers = $insertMissingSubscribers;
    }

    /**
     * @param string|null $campaign
     *
     * @return MindbazTransport
     */
    public function setCampaign($campaign = null)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @param bool $insertMissingSubscribers
     *
     * @return MindbazTransport
     */
    public function setInsertMissingSubscribers($insertMissingSubscribers)
    {
        $this->insertMissingSubscribers = $insertMissingSubscribers;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        // Security: a valid campaign is required
        if (null === $this->campaign || !array_key_exists($this->campaign, $this->campaigns)) {
            throw new InvalidCampaignException();
        }

        // Find subscribers by email addresses
        $emails = array_keys($message->getTo());
        $subscribers = $this->subscriberManager->findByEmail($emails);

        $invalid = array_diff($emails, array_map(function (Subscriber $subscriber) {
            return $subscriber->getEmail();
        }, $subscribers));

        // Don't insert missing subscribers
        if (false === $this->insertMissingSubscribers && 0 < count($invalid)) {
            throw new MissingSubscribersException($invalid);
        }

        // Insert missing subscribers
        foreach ($invalid as $email) {
            $subscribers[] = $this->subscriberManager->create(['email' => $email]);
        }

        // Send email
        foreach ($subscribers as $subscriber) {
            $this->subscriberManager->send($this->campaigns[$this->campaign], $subscriber, $message);
        }

        return count($subscribers);
    }

    /**
     * {@inheritdoc}
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        $this->eventDispatcher->bindEventListener($plugin);
    }
}
