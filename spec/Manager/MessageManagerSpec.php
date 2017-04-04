<?php

namespace spec\MindbazBundle\Manager;

use mbzOneshot\OneshotWebService;
use mbzOneshot\Send;
use mbzOneshot\SendResponse;
use MindbazBundle\Exception\SendErrorException;
use MindbazBundle\Manager\MessageManager;
use MindbazBundle\Model\Subscriber;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class MessageManagerSpec extends ObjectBehavior
{
    function let(OneshotWebService $oneshotWebService, LoggerInterface $logger)
    {
        $this->beConstructedWith($oneshotWebService, $logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MessageManager::class);
    }

    function it_successfully_sends_a_message(OneshotWebService $oneshotWebService, SendResponse $response, Subscriber $subscriber, \Swift_Mime_Message $message, \Swift_Mime_MimeEntity $child, LoggerInterface $logger)
    {
        $subscriber->getId()->willReturn(456)->shouldBeCalledTimes(2);
        $message->getChildren()->willReturn([$child])->shouldBeCalledTimes(1);
        $child->getContentType()->willReturn('text/plain')->shouldBeCalledTimes(1);
        $child->getBody()->willReturn('Foo')->shouldBeCalledTimes(1);
        $message->getContentType()->willReturn('text/html')->shouldBeCalledTimes(2);
        $message->getBody()->willReturn('<p>Foo</p>')->shouldBeCalledTimes(1);
        $message->getSender()->willReturn('noreply@example.com')->shouldBeCalledTimes(1);
        $message->getSubject()->willReturn('Bar')->shouldBeCalledTimes(1);
        $oneshotWebService->Send(new Send(
            123,
            456,
            '<p>Foo</p>',
            'Foo',
            'noreply@example.com',
            'Bar'
        ))->willReturn($response)->shouldBeCalledTimes(1);
        $response->getSendResult()->willReturn(MessageManager::MINDBAZ_SEND_RESPONSE_OK)->shouldBeCalledTimes(1);
        $logger->info('Message successfully sent to subscriber', ['id' => 456])->shouldBeCalledTimes(1);

        $this->send(123, $subscriber, $message);
    }

    function it_unsuccessfully_sends_a_message(OneshotWebService $oneshotWebService, SendResponse $response, Subscriber $subscriber, \Swift_Mime_Message $message, LoggerInterface $logger)
    {
        $subscriber->getId()->willReturn(456)->shouldBeCalledTimes(2);
        $message->getChildren()->willReturn([])->shouldBeCalledTimes(1);
        $message->getContentType()->willReturn('text/html')->shouldBeCalledTimes(2);
        $message->getBody()->willReturn('<p>Foo</p>')->shouldBeCalledTimes(1);
        $message->getSender()->willReturn('noreply@example.com')->shouldBeCalledTimes(1);
        $message->getSubject()->willReturn('Bar')->shouldBeCalledTimes(1);
        $oneshotWebService->Send(new Send(
            123,
            456,
            '<p>Foo</p>',
            null,
            'noreply@example.com',
            'Bar'
        ))->willReturn($response)->shouldBeCalledTimes(1);
        $response->getSendResult()->willReturn(MessageManager::MINDBAZ_SEND_RESPONSE_NOK)->shouldBeCalledTimes(2);
        $logger->error('An error occurred while sending the message to subscriber', ['id' => 456, 'response' => MessageManager::MINDBAZ_SEND_RESPONSE_NOK])->shouldBeCalledTimes(1);

        $this->shouldThrow(SendErrorException::class)->during('send', [123, $subscriber, $message]);
    }
}
