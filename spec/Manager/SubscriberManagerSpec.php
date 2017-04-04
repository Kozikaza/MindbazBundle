<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\MindbazBundle\Manager;

use mbzSubscriber\ArrayOfInt;
use mbzSubscriber\ArrayOfString;
use mbzSubscriber\ArrayOfSubscriber;
use mbzSubscriber\GetSubscribersByEmail;
use mbzSubscriber\GetSubscribersByEmailResponse;
use mbzSubscriber\InsertSubscriber;
use mbzSubscriber\InsertSubscriberResponse;
use mbzSubscriber\Subscriber as MindbazSubscriber;
use mbzSubscriber\SubscriberWebService;
use mbzSubscriber\Unsubscribe;
use mbzSubscriber\UnsubscribeResponse;
use MindbazBundle\Manager\SubscriberManager;
use MindbazBundle\Model\Subscriber;
use MindbazBundle\Serializer\Bridge\Serializer;
use MindbazBundle\Serializer\SubscriberEncoder;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class SubscriberManagerSpec extends ObjectBehavior
{
    function let(SubscriberWebService $subscriberWebService, Serializer $serializer, LoggerInterface $logger)
    {
        $this->beConstructedWith($subscriberWebService, $serializer, $logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SubscriberManager::class);
    }

    function it_creates_and_inserts_a_subscriber_from_data(Serializer $serializer, SubscriberWebService $subscriberWebService, InsertSubscriberResponse $response, LoggerInterface $logger, Subscriber $subscriber, MindbazSubscriber $mindbazSubscriber)
    {
        $serializer->denormalize([
            'email'     => 'foo@example.com',
            'firstName' => 'John',
            'lastName'  => 'DOE',
        ], Subscriber::class)->willReturn($subscriber)->shouldBeCalledTimes(1);
        $serializer->serialize($subscriber, SubscriberEncoder::FORMAT)->willReturn($mindbazSubscriber)->shouldBeCalledTimes(1);
        $subscriberWebService->InsertSubscriber(new InsertSubscriber($mindbazSubscriber->getWrappedObject(), true))->willReturn($response)->shouldBeCalledTimes(1);
        $response->getInsertSubscriberResult()->willReturn(123)->shouldBeCalledTimes(1);
        $subscriber->setId(123)->shouldBeCalledTimes(1);
        $subscriber->getId()->willReturn(123)->shouldBeCalledTimes(1);
        $logger->info('New subscriber inserted in Mindbaz', ['id' => 123])->shouldBeCalledTimes(1);

        $this->create([
            'email'     => 'foo@example.com',
            'firstName' => 'John',
            'lastName'  => 'DOE',
        ]);
    }

    function it_successfully_unsubscribes_a_subscriber(SubscriberWebService $subscriberWebService, UnsubscribeResponse $response, LoggerInterface $logger, Subscriber $subscriber, MindbazSubscriber $mindbazSubscriber)
    {
        $subscriber->getId()->willReturn(123)->shouldBeCalledTimes(2);
        $subscriberWebService->Unsubscribe(new Unsubscribe(123, null, null))->willReturn($response)->shouldBeCalledTimes(1);
        $response->getUnsubscribeResult()->willReturn(true)->shouldBeCalledTimes(1);
        $logger->info('Subscriber successfully unsubscribed', ['id' => 123])->shouldBeCalledTimes(1);
        $logger->error('An error occurred while unsubscribing subscriber', ['id' => 123, 'response' => false])->shouldNotBeCalled();

        $this->unsubscribe($subscriber);
    }

    function it_doesnt_unsubscribes_a_subscriber(SubscriberWebService $subscriberWebService, UnsubscribeResponse $response, LoggerInterface $logger, Subscriber $subscriber)
    {
        $subscriber->getId()->willReturn(123)->shouldBeCalledTimes(2);
        $subscriberWebService->Unsubscribe(new Unsubscribe(123, null, null))->willReturn($response)->shouldBeCalledTimes(1);
        $response->getUnsubscribeResult()->willReturn(false)->shouldBeCalledTimes(2);
        $logger->info('Subscriber successfully unsubscribed', ['id' => 123])->shouldNotBeCalled();
        $logger->error('An error occurred while unsubscribing subscriber', ['id' => 123, 'response' => false])->shouldBeCalledTimes(1);

        $this->unsubscribe($subscriber);
    }

    function it_finds_no_subscribers_by_email(SubscriberWebService $subscriberWebService, GetSubscribersByEmailResponse $response)
    {
        $subscriberWebService->GetSubscribersByEmail(new GetSubscribersByEmail(
            (new ArrayOfString())->setString(['foo@example.com']),
            (new ArrayOfInt())->setInt([0, 1])
        ))->willReturn($response)->shouldBeCalledTimes(1);
        $response->getGetSubscribersByEmailResult()->shouldBeCalledTimes(1);

        $this->findByEmail(['foo@example.com'])->shouldBeEqualTo([]);
    }

    function it_finds_subscribers_by_email(Serializer $serializer, SubscriberWebService $subscriberWebService, GetSubscribersByEmailResponse $response, ArrayOfSubscriber $subscribers, Subscriber $subscriber, MindbazSubscriber $mindbazSubscriber)
    {
        $subscriberWebService->GetSubscribersByEmail(new GetSubscribersByEmail(
            (new ArrayOfString())->setString(['foo@example.com']),
            (new ArrayOfInt())->setInt([0, 1])
        ))->willReturn($response)->shouldBeCalledTimes(1);
        $response->getGetSubscribersByEmailResult()->willReturn($subscribers)->shouldBeCalledTimes(1);
        $subscribers->getSubscriber()->willReturn([$mindbazSubscriber])->shouldBeCalledTimes(1);
        $serializer->deserialize($mindbazSubscriber, Subscriber::class, SubscriberEncoder::FORMAT)->willReturn($subscriber)->shouldBeCalledTimes(1);
        ;

        $this->findByEmail(['foo@example.com'])->shouldBeEqualTo([$subscriber]);
    }

    function it_does_not_find_one_subscriber_by_email(SubscriberWebService $subscriberWebService, GetSubscribersByEmailResponse $response)
    {
        $subscriberWebService->GetSubscribersByEmail(new GetSubscribersByEmail(
            (new ArrayOfString())->setString(['foo@example.com']),
            (new ArrayOfInt())->setInt([0, 1])
        ))->willReturn($response)->shouldBeCalledTimes(1);
        $response->getGetSubscribersByEmailResult()->shouldBeCalledTimes(1);

        $this->findOneByEmail('foo@example.com')->shouldBeNull();
    }

    function it_finds_one_subscriber_by_email(Serializer $serializer, SubscriberWebService $subscriberWebService, GetSubscribersByEmailResponse $response, ArrayOfSubscriber $subscribers, Subscriber $subscriber, MindbazSubscriber $mindbazSubscriber)
    {
        $subscriberWebService->GetSubscribersByEmail(new GetSubscribersByEmail(
            (new ArrayOfString())->setString(['foo@example.com']),
            (new ArrayOfInt())->setInt([0, 1])
        ))->willReturn($response)->shouldBeCalledTimes(1);
        $response->getGetSubscribersByEmailResult()->willReturn($subscribers)->shouldBeCalledTimes(1);
        $subscribers->getSubscriber()->willReturn([$mindbazSubscriber])->shouldBeCalledTimes(1);
        $serializer->deserialize($mindbazSubscriber, Subscriber::class, SubscriberEncoder::FORMAT)->willReturn($subscriber)->shouldBeCalledTimes(1);
        ;

        $this->findOneByEmail('foo@example.com')->shouldBeEqualTo($subscriber);
    }

    function getMatchers()
    {
        return [
            'beNull' => function ($subject) {
                return null === $subject;
            },
        ];
    }
}
