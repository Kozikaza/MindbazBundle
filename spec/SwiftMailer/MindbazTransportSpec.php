<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Kozikaza\MindbazBundle\SwiftMailer;

use Kozikaza\MindbazBundle\Exception\InvalidCampaignException;
use Kozikaza\MindbazBundle\Exception\MissingSubscribersException;
use Kozikaza\MindbazBundle\Manager\MessageManager;
use Kozikaza\MindbazBundle\Manager\SubscriberManager;
use Kozikaza\MindbazBundle\Model\Subscriber;
use Kozikaza\MindbazBundle\SwiftMailer\MindbazTransport;
use PhpSpec\ObjectBehavior;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MindbazTransportSpec extends ObjectBehavior
{
    function let(SubscriberManager $subscriberManager, MessageManager $messageManager, \Swift_Events_EventDispatcher $eventDispatcher)
    {
        $this->beConstructedWith($subscriberManager, $messageManager, $eventDispatcher, ['register' => 10], false);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MindbazTransport::class);
    }

    function it_sends_a_message_to_a_subscriber_in_a_campaign(SubscriberManager $subscriberManager, MessageManager $messageManager, \Swift_Mime_Message $message, Subscriber $subscriber)
    {
        $message->getTo()->willReturn(['foo@example.com' => null])->shouldBeCalledTimes(1);
        $subscriberManager->findByEmail(['foo@example.com'])->willReturn([$subscriber])->shouldBeCalledTimes(1);
        $subscriber->getEmail()->willReturn('foo@example.com')->shouldBeCalledTimes(1);
        $subscriberManager->create(['email' => 'foo@example.com'])->shouldNotBeCalled();
        $messageManager->send(10, $subscriber, $message)->shouldBeCalledTimes(1);

        $this->setCampaign('register');
        $this->send($message)->shouldBeEqualTo(1);
    }

    function it_throws_an_exception_if_no_campaign_has_been_set(\Swift_Mime_Message $message)
    {
        $this->shouldThrow(InvalidCampaignException::class)->during('send', [$message]);
    }

    function it_throws_an_exception_if_missing_subscribers_are_found_and_insert_option_not_set(SubscriberManager $subscriberManager, MessageManager $messageManager, \Swift_Mime_Message $message, Subscriber $subscriber)
    {
        $message->getTo()->willReturn(['foo@example.com' => null])->shouldBeCalledTimes(1);
        $subscriberManager->findByEmail(['foo@example.com'])->willReturn([])->shouldBeCalledTimes(1);
        $subscriber->getEmail()->shouldNotBeCalled();
        $subscriberManager->create(['email' => 'foo@example.com'])->shouldNotBeCalled();
        $messageManager->send(10, $subscriber, $message)->shouldNotBeCalled();

        $this->setCampaign('register');
        $this->shouldThrow(MissingSubscribersException::class)->during('send', [$message]);
    }

    function it_creates_missing_subscribers_if_insert_option_is_set(SubscriberManager $subscriberManager, MessageManager $messageManager, \Swift_Mime_Message $message, Subscriber $subscriber)
    {
        $message->getTo()->willReturn(['foo@example.com' => null])->shouldBeCalledTimes(1);
        $subscriberManager->findByEmail(['foo@example.com'])->willReturn([])->shouldBeCalledTimes(1);
        $subscriber->getEmail()->shouldNotBeCalled();
        $subscriberManager->create(['email' => 'foo@example.com'])->willReturn($subscriber)->shouldBeCalledTimes(1);
        $messageManager->send(10, $subscriber, $message)->shouldBeCalledTimes(1);

        $this->setCampaign('register');
        $this->setInsertMissingSubscribers(true);
        $this->send($message);
    }

    function it_registers_plugin(\Swift_Events_EventDispatcher $eventDispatcher, \Swift_Events_EventListener $plugin)
    {
        $eventDispatcher->bindEventListener($plugin)->shouldBeCalledTimes(1);
        $this->registerPlugin($plugin);
    }

    function it_is_started()
    {
        $this->isStarted()->shouldBeTrue();
    }

    function it_starts()
    {
        $this->start()->shouldBeNull();
    }

    function it_stops()
    {
        $this->stop()->shouldBeNull();
    }

    function getMatchers()
    {
        return [
            'beTrue' => function ($subject) {
                return true === $subject;
            },
            'beFalse' => function ($subject) {
                return false === $subject;
            },
            'beNull' => function ($subject) {
                return null === $subject;
            },
        ];
    }
}
