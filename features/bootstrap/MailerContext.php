<?php

use Behat\Behat\Context\Context;
use Dubture\Monolog\Reader\LogReader;
use MindbazBundle\Exception\InvalidCampaignException;
use MindbazBundle\Exception\MissingSubscribersException;
use MindbazBundle\Manager\SubscriberManager;
use MindbazBundle\SwiftMailer\MindbazTransport;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Log\LoggerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MailerContext implements Context
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \LogicException
     */
    private $exception;

    /**
     * @var MindbazTransport
     */
    private $transport;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubscriberManager
     */
    private $manager;

    /**
     * @var string
     */
    private $randomEmail;

    public function __construct(\Swift_Mailer $mailer, LoggerInterface $logger, SubscriberManager $manager)
    {
        $this->mailer = $mailer;
        $this->transport = $mailer->getTransport();
        $this->logger = $logger;
        $this->manager = $manager;
        $this->randomEmail = sprintf('foo%d@example.com', rand());
    }

    /**
     * @BeforeScenario
     */
    public function reset()
    {
        // Reset saved exception
        $this->exception = null;

        // Reset campaign
        $this->transport->setCampaign(null);

        // Reset subscribers creation option
        $this->transport->setInsertMissingSubscribers(false);

        // Clear logs
        if (is_file(__DIR__.'/../app/logs/mindbaz.log')) {
            unlink(__DIR__.'/../app/logs/mindbaz.log');
        }
    }

    /**
     * @AfterScenario
     */
    public function resetSubscribers()
    {
        $subscriber = $this->manager->findOneByEmail($this->randomEmail);
        if (null !== $subscriber) {
            $this->manager->unsubscribe($subscriber);
        }
    }

    /**
     * @Given I set a campaign
     */
    public function ISetACampaign()
    {
        $this->transport->setCampaign('register');
    }

    /**
     * @Given I allow to insert missing subscribers
     */
    public function IAllowToInsertMissingSubscribers()
    {
        $this->transport->setInsertMissingSubscribers(true);
    }

    /**
     * @When I send an email
     * @When I send an email to an existing subscriber
     */
    public function ISendAnEmailToAnExistingSubscriber()
    {
        $message = new \Swift_Message('MindbazBundle test title', <<<'HTML'
<p>Mindbaz body message</p>
HTML
            , 'text/html');
        $message->setFrom('noreply@example.com');
        $message->addTo('vincent@les-tilleuls.coop');
        try {
            if (0 === $this->mailer->send($message)) {
                throw new \RuntimeException('Unable to send email');
            }
        } catch (InvalidCampaignException $e) {
            $this->exception = $e;
        }
    }

    /**
     * @When I send an email to a non-existing subscriber
     */
    public function ISendAnEmailToANonExistingSubscriber()
    {
        $message = new \Swift_Message('MindbazBundle test title', <<<'HTML'
<p>Mindbaz body message</p>
HTML
        , 'text/html');
        $message->setFrom('noreply@example.com');
        $message->addTo($this->randomEmail);
        try {
            if (0 === $this->mailer->send($message)) {
                throw new \RuntimeException('Unable to send email');
            }
        } catch (InvalidCampaignException $e) {
            $this->exception = $e;
        } catch (MissingSubscribersException $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Then an email should be sent to this user
     */
    public function AnEmailShouldBeSentToThisUser()
    {
        $reader = new LogReader(__DIR__.'/../app/logs/mindbaz.log');

        foreach ($reader as $log) {
            // Last row of log file is empty: ignore it
            if (0 === count($log)) {
                continue;
            }

            if (isset($log['message']) && 'Message successfully sent to subscriber' === $log['message'] && isset($log['context']['id'])) {
                return true;
            }
        }

        throw new ExpectationFailedException('Unable to find log entry: email may not have been sent');
    }

    /**
     * @Then this user should have been created on Mindbaz
     */
    public function thisUserShouldHaveBeenCreatedOnMindbaz()
    {
        \PHPUnit\Framework\Assert::assertNotNull($this->manager->findOneByEmail($this->randomEmail));
    }

    /**
     * @Then I should get an error
     */
    public function IShouldGetAnError()
    {
        \PHPUnit\Framework\Assert::assertNotNull($this->exception, 'No exception has been thrown.');
        \PHPUnit\Framework\Assert::assertInstanceOf('\LogicException', $this->exception);
    }
}
