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

use Kozikaza\MindbazBundle\Exception\SendErrorException;
use Kozikaza\MindbazBundle\Model\Subscriber;
use mbzOneshot\OneshotWebService;
use mbzOneshot\Send;
use Psr\Log\LoggerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MessageManager
{
    const MINDBAZ_SEND_RESPONSE_OK = 'OK';
    const MINDBAZ_SEND_RESPONSE_NOK = 'NOK';

    /**
     * @var OneshotWebService
     */
    private $oneshotWebService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param OneshotWebService $oneshotWebService
     * @param LoggerInterface   $logger
     */
    public function __construct(OneshotWebService $oneshotWebService, LoggerInterface $logger)
    {
        $this->oneshotWebService = $oneshotWebService;
        $this->logger = $logger;
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
