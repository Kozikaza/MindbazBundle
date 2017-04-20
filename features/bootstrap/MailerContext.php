<?php

/*
 * This file is part of the MindbazBundle package.
 *
 * (c) David DELEVOYE <david.delevoye@adeo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Behat\Behat\Context\Context;
use Dubture\Monolog\Reader\LogReader;
use Gorghoa\ScenarioStateBehatExtension\Annotation\ScenarioStateArgument;
use Gorghoa\ScenarioStateBehatExtension\Context\ScenarioStateAwareContext;
use Gorghoa\ScenarioStateBehatExtension\Context\ScenarioStateAwareTrait;
use Kozikaza\MindbazBundle\Exception\InvalidCampaignException;
use Kozikaza\MindbazBundle\Exception\MissingSubscribersException;
use Kozikaza\MindbazBundle\Manager\SubscriberManager;
use Kozikaza\MindbazBundle\Model\Subscriber;
use Kozikaza\MindbazBundle\SwiftMailer\MindbazTransport;
use Psr\Log\LoggerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class MailerContext implements Context, ScenarioStateAwareContext
{
    use ScenarioStateAwareTrait;

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

    public function __construct(\Swift_Mailer $mailer, LoggerInterface $logger, SubscriberManager $manager)
    {
        $this->mailer = $mailer;
        $this->transport = $mailer->getTransport();
        $this->logger = $logger;
        $this->manager = $manager;
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
        if ($this->scenarioState->hasStateFragment('email')) {
            $subscriber = $this->manager->findOneByEmail($this->scenarioState->getStateFragment('email'));
            if (null !== $subscriber) {
                $this->manager->unsubscribe($subscriber);
            }
        }
    }

    /**
     * @Given I set a campaign
     */
    public function ISetACampaign()
    {
        $this->transport->setCampaign('kzkz-test');
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
     * @When I send another email to the same address
     *
     * @ScenarioStateArgument("email")
     *
     * @param string $email
     */
    public function ISendAnEmailToAnExistingSubscriber($email = 'vincent@les-tilleuls.coop')
    {
        $message = new \Swift_Message('MindbazBundle test title', <<<'HTML'
<p>Mindbaz body message</p>
HTML
            , 'text/html');
        $message->setFrom('noreply@example.com');
        $message->addTo($email);
        $message->addPart('Mindbaz body message', 'text/plain');
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
        // Generate email
        $email = sprintf('foo%d@example.com', rand());
        $this->scenarioState->provideStateFragment('email', $email);

        $message = new \Swift_Message('MindbazBundle test title', <<<'HTML'
<p>Mindbaz body message</p>
HTML
        , 'text/html');
        $message->setFrom('noreply@example.com');
        $message->addTo($email);
        $message->addPart('Mindbaz body message', 'text/plain');
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
     * @When I send an email to a non-existing subscriber with an uppercase address
     */
    public function ISendAnEmailToANonExistingSubscriberWithAnUppercaseAddress()
    {
        // Generate email
        $email = sprintf('fOo%d@example.com', rand());
        $this->scenarioState->provideStateFragment('email', $email);

        $message = new \Swift_Message('MindbazBundle test title', <<<'HTML'
<p>Mindbaz body message</p>
HTML
        , 'text/html');
        $message->setFrom('noreply@example.com');
        $message->addTo($email);
        $message->addPart('Mindbaz body message', 'text/plain');
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
    public function anEmailShouldBeSentToThisUser()
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

        throw new PHPUnit_Framework_ExpectationFailedException('Unable to find log entry: email may not have been sent');
    }

    /**
     * @Then this user should have been created on Mindbaz
     *
     * @ScenarioStateArgument("email")
     *
     * @param string $email
     */
    public function thisUserShouldHaveBeenCreatedOnMindbaz($email)
    {
        $subscriber = $this->manager->findOneByEmail($email);
        \PHPUnit\Framework\Assert::assertNotNull($subscriber);
        $this->scenarioState->provideStateFragment('subscriber', $subscriber);
    }

    /**
     * @Then its email address should have been lowercased
     *
     * @ScenarioStateArgument("subscriber")
     * @ScenarioStateArgument("email")
     *
     * @param Subscriber $subscriber
     * @param string     $email
     */
    public function itsEmailAddressShouldHaveBeenLowercased(Subscriber $subscriber, $email)
    {
        \PHPUnit\Framework\Assert::assertRegExp('/^[^A-Z]+$/', $subscriber->getEmail());
        \PHPUnit\Framework\Assert::assertEquals(strtolower($email), $subscriber->getEmail());
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
