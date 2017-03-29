<?php

namespace spec\MindbazBundle\SwiftMailer;

use MindbazBundle\Exception\InvalidCampaignException;
use MindbazBundle\Exception\MissingSubscribersException;
use MindbazBundle\Manager\SubscriberManager;
use MindbazBundle\Model\Subscriber;
use MindbazBundle\SwiftMailer\MindbazTransport;
use PhpSpec\ObjectBehavior;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MindbazTransportSpec extends ObjectBehavior
{
    public function let(SubscriberManager $subscriberManager, \Swift_Events_EventDispatcher $eventDispatcher)
    {
        $this->beConstructedWith($subscriberManager, $eventDispatcher, ['register' => 10], false);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MindbazTransport::class);
    }

    public function it_sends_a_message_to_a_subscriber_in_a_campaign(SubscriberManager $subscriberManager, \Swift_Mime_Message $message, Subscriber $subscriber)
    {
        $message->getTo()->willReturn(['foo@example.com' => null])->shouldBeCalledTimes(1);
        $subscriberManager->findByEmail(['foo@example.com'])->willReturn([$subscriber])->shouldBeCalledTimes(1);
        $subscriber->getEmail()->willReturn('foo@example.com')->shouldBeCalledTimes(1);
        $subscriberManager->create(['email' => 'foo@example.com'])->shouldNotBeCalled();
        $subscriberManager->send(10, $subscriber, $message)->shouldBeCalledTimes(1);

        $this->setCampaign('register');
        $this->send($message)->shouldBeEqualTo(1);
    }

    public function it_throws_an_exception_if_no_campaign_has_been_set(\Swift_Mime_Message $message)
    {
        $this->shouldThrow(InvalidCampaignException::class)->during('send', [$message]);
    }

    public function it_throws_an_exception_if_missing_subscribers_are_found_and_insert_option_not_set(SubscriberManager $subscriberManager, \Swift_Mime_Message $message, Subscriber $subscriber)
    {
        $message->getTo()->willReturn(['foo@example.com' => null])->shouldBeCalledTimes(1);
        $subscriberManager->findByEmail(['foo@example.com'])->willReturn([])->shouldBeCalledTimes(1);
        $subscriber->getEmail()->shouldNotBeCalled();
        $subscriberManager->create(['email' => 'foo@example.com'])->shouldNotBeCalled();
        $subscriberManager->send(10, $subscriber, $message)->shouldNotBeCalled();

        $this->setCampaign('register');
        $this->shouldThrow(MissingSubscribersException::class)->during('send', [$message]);
    }

    public function it_creates_missing_subscribers_if_insert_option_is_set(SubscriberManager $subscriberManager, \Swift_Mime_Message $message, Subscriber $subscriber)
    {
        $message->getTo()->willReturn(['foo@example.com' => null])->shouldBeCalledTimes(1);
        $subscriberManager->findByEmail(['foo@example.com'])->willReturn([])->shouldBeCalledTimes(1);
        $subscriber->getEmail()->shouldNotBeCalled();
        $subscriberManager->create(['email' => 'foo@example.com'])->willReturn($subscriber)->shouldBeCalledTimes(1);
        $subscriberManager->send(10, $subscriber, $message)->shouldBeCalledTimes(1);

        $this->setCampaign('register');
        $this->setInsertMissingSubscribers(true);
        $this->send($message);
    }

    public function it_registers_plugin(\Swift_Events_EventDispatcher $eventDispatcher, \Swift_Events_EventListener $plugin)
    {
        $eventDispatcher->bindEventListener($plugin)->shouldBeCalledTimes(1);
        $this->registerPlugin($plugin);
    }

    public function it_is_started()
    {
        $this->isStarted()->shouldBeTrue();
    }

    public function it_starts()
    {
        $this->start()->shouldBeNull();
    }

    public function it_stops()
    {
        $this->stop()->shouldBeNull();
    }

    public function getMatchers()
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
