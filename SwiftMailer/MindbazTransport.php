<?php

namespace MindbazBundle\SwiftMailer;

use mbzOneshot\OneshotWebService;
use mbzOneshot\Send;
use mbzSubscriber\GetSubscriberByEmail;
use mbzSubscriber\SubscriberWebService;

class MindbazTransport implements \Swift_Transport
{
    /**
     * @var \Swift_Events_EventDispatcher
     */
    private $dispatcher;

    /**
     * @var OneshotWebService
     */
    private $oneshotService;

    /**
     * @var SubscriberWebService
     */
    private $subscriberService;

    /**
     * @var array
     */
    private $resultApi;

    /**
     * @param \Swift_Events_EventDispatcher $dispatcher
     */
    public function __construct(\Swift_Events_EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return false;
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
        $this->resultApi = null;

        if ($event = $this->dispatcher->createSendEvent($this, $message)) {
            $this->dispatcher->dispatchEvent($event, 'beforeSendPerformed');
            if ($event->bubbleCancelled()) {
                return 0;
            }
        }

        $sendCount = 0;
        $mindbazMessage = $this->getMindbazMessage($message);
        $sendResult = $this->getOneshotService()->Send($mindbazMessage);

        $this->resultApi = $sendResult->getSendResult();

        /*
        foreach ($this->resultApi as $item) {
            if ($item['status'] === 'sent' || $item['status'] === 'queued') {
                $sendCount++;
            } else {
                $failedRecipients[] = $item['email'];
            }
        }

        if ($event) {
            if ($sendCount > 0) {
                $event->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
            } else {
                $event->setResult(Swift_Events_SendEvent::RESULT_FAILED);
            }
            $this->dispatcher->dispatchEvent($event, 'sendPerformed');
        }

        return $sendCount;
        */
    }

    /**
     * @param \Swift_Mime_Message $message
     *
     * @return Send
     */
    public function getMindbazMessage(\Swift_Mime_Message $message)
    {
        if (!$message->getHeaders()->has('X-MBZ-Campaign')) {
            throw new \RuntimeException('Campaign ID must be defined in header "X-MBZ-Campaign"');
        }

        $campaignId = $message->getHeaders()->get('X-MBZ-Campaign')->getValue();

        $contentType = $this->getMessagePrimaryContentType($message);
        $fromAddresses = $message->getFrom();
        $toAddresses = $message->getTo();

        $fromName = current($fromAddresses);
        $to = key($toAddresses);

        $bodyHtml = $bodyText = null;
        if ($contentType === 'text/plain') {
            $bodyText = $message->getBody();
        } elseif ($contentType === 'text/html') {
            $bodyHtml = $message->getBody();
        } else {
            $bodyHtml = $message->getBody();
        }

        foreach ($message->getChildren() as $child) {
            if ($child instanceof \Swift_MimePart && $this->supportsContentType($child->getContentType())) {
                if ($child->getContentType() == "text/html") {
                    $bodyHtml = $child->getBody();
                } elseif ($child->getContentType() == "text/plain") {
                    $bodyText = $child->getBody();
                }
            }
        }

        if ($message->getHeaders()->has('List-Unsubscribe')) {
            $headers['List-Unsubscribe'] = $message->getHeaders()->get('List-Unsubscribe')->getValue();
        }

        $mindbazMessage = new Send(
            $campaignId,
            $this->getSubscriber($to)->getIdSubscriber(),
            $bodyHtml,
            $bodyText,
            $fromName,
            $message->getSubject()
        );

        return $mindbazMessage;
    }

    /**
     * @param string $email
     *
     * @return \mbzSubscriber\Subscriber
     */
    public function getSubscriber($email)
    {
        $subscribedEmail = new GetSubscriberByEmail($email, null);
        $subscribedIdResult = $this->getSubscriberService()->GetSubscriberByEmail($subscribedEmail);

        return $subscribedIdResult->getGetSubscriberByEmailResult();
    }

    /**
     * @return null|SubscriberWebService
     */
    public function getSubscriberService()
    {
        return $this->subscriberService;
    }

    /**
     * @param SubscriberWebService $service
     *
     * @return $this
     */
    public function setSubscriberService(SubscriberWebService $service)
    {
        $this->subscriberService = $service;

        return $this;
    }

    /**
     * @return null|OneshotWebService
     */
    public function getOneshotService()
    {
        return $this->oneshotService;
    }

    /**
     * @param OneshotWebService $service
     *
     * @return $this
     */
    public function setOneshotService(OneshotWebService $service)
    {
        $this->oneshotService = $service;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        $this->dispatcher->bindEventListener($plugin);
    }

    /**
     * @return null|array
     */
    public function getResultApi()
    {
        return $this->resultApi;
    }

    /**
     * @param \Swift_Mime_Message $message
     *
     * @return string
     */
    private function getMessagePrimaryContentType(\Swift_Mime_Message $message)
    {
        $contentType = $message->getContentType();
        if ($this->supportsContentType($contentType)) {
            return $contentType;
        }

        // SwiftMailer hides the content type set in the constructor of \Swift_Mime_Message as soon
        // as you add another part to the message. We need to access the protected property
        // _userContentType to get the original type.
        $messageRef = new \ReflectionClass($message);
        if ($messageRef->hasProperty('_userContentType')) {
            $propRef = $messageRef->getProperty('_userContentType');
            $propRef->setAccessible(true);
            $contentType = $propRef->getValue($message);
        }

        return $contentType;
    }

    /**
     * @param string $contentType
     *
     * @return bool
     */
    private function supportsContentType($contentType)
    {
        return in_array($contentType, ['text/plain', 'text/html']);
    }
}
