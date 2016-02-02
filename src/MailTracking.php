<?php

namespace Spinen\MailAssertions;

use Illuminate\Support\Facades\Mail;
use PHPUnit_Framework_TestCase;
use Swift_Message;

/**
 * Class MailTracking
 *
 * Trait to mixin to your test to allow for custom assertions when using PHPUnit with Laravel.
 *
 * This originally started out as a copy & paste from a video series that Jeffery Way did on laracasts.com.  If you do
 * not have an account on Laracasts, you should get one.  It is an amazing resource to learn from.  We used that
 * example & converted it to a package so that it would be easy to install.  We have also expanded on initial
 * assertions.
 *
 * I WANT IT CLEAR THAT THIS WOULD NOT HAVE HAPPENED WITHOUT THE INITIAL WORK OF JEFFERY WAY.  WE ARE NOT CLAIMING TO
 * BE THE CREATORS OF THE CONCEPT.
 *
 * @package Spinen\MailAssertions
 * @see     https://gist.github.com/JeffreyWay/b501c53d958b07b8a332
 * @tutorial https://laracasts.com/series/phpunit-testing-in-laravel/episodes/12
 */
trait MailTracking
{
    /**
     * Delivered emails.
     *
     * @var array
     */
    protected $emails = [];

    /**
     * Register a listener for new emails.
     *
     * Called my PHPUnit before each test it run.  It registers the MailRecorder "plugin" with Swift, so that we can
     * get a copy of each email that is sent during that test.
     *
     * @before
     */
    public function setUpMailTracking()
    {
        Mail::getSwiftMailer()
            ->registerPlugin(new MailRecorder($this));
    }

    /**
     * Retrieve the appropriate swift message.
     *
     * @param Swift_Message $message
     *
     * @return Swift_Message
     */
    protected function getEmail(Swift_Message $message = null)
    {
        $this->seeEmailWasSent();

        return $message ?: $this->lastEmail();
    }

    /**
     * Retrieve the mostly recently sent swift message.
     */
    protected function lastEmail()
    {
        return end($this->emails);
    }

    /**
     * Store a new swift message.
     *
     * Collection of emails that were received by the MailRecorder plugin during a test.
     *
     * @param Swift_Message $email
     */
    public function recordMail(Swift_Message $email)
    {
        $this->emails[] = $email;
    }

    /**
     * Assert that the last email's body contains the given text.
     *
     * @param string        $excerpt
     * @param Swift_Message $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailContains($excerpt, Swift_Message $message = null)
    {
        $this->assertContains($excerpt, $this->getEmail($message)
                                             ->getBody(), "No email containing the provided body was found.");

        return $this;
    }

    /**
     * Assert that the last email's body equals the given text.
     *
     * @param string        $body
     * @param Swift_Message $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailEquals($body, Swift_Message $message = null)
    {
        $this->assertEquals($body, $this->getEmail($message)
                                        ->getBody(), "No email with the provided body was sent.");

        return $this;
    }

    /**
     * Assert that the last email was delivered by the given address.
     *
     * @param string        $sender
     * @param Swift_Message $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailFrom($sender, Swift_Message $message = null)
    {
        // TODO: Allow from to be an array to check email & name
        $this->assertArrayHasKey($sender, (array)$this->getEmail($message)
                                                      ->getFrom(), "No email was sent from $sender.");

        return $this;
    }

    /**
     * Assert that the given number of emails were sent.
     *
     * @param integer $count
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailsSent($count)
    {
        $emailsSent = count($this->emails);

        $this->assertCount($count, $this->emails, "Expected $count emails to have been sent, but $emailsSent were.");

        return $this;
    }

    /**
     * Assert that the last email's subject matches the given string.
     *
     * @param string        $subject
     * @param Swift_Message $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailSubject($subject, Swift_Message $message = null)
    {
        // TODO: Consider a subject contains like the message contains
        $this->assertEquals($subject, $this->getEmail($message)
                                           ->getSubject(), "No email with a subject of $subject was found.");

        return $this;
    }

    /**
     * Assert that the last email was sent to the given recipient.
     *
     * @param string        $recipient
     * @param Swift_Message $message
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailTo($recipient, Swift_Message $message = null)
    {
        $this->assertArrayHasKey($recipient, (array)$this->getEmail($message)
                                                         ->getTo(), "No email was sent to $recipient.");

        return $this;
    }

    /**
     * Assert that no emails were sent.
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailWasNotSent()
    {
        $this->assertEmpty($this->emails, 'Did not expect any emails to have been sent.');

        return $this;
    }

    /**
     * Assert that at least one email was sent.
     *
     * @return PHPUnit_Framework_TestCase $this
     */
    protected function seeEmailWasSent()
    {
        $this->assertNotEmpty($this->emails, 'No emails have been sent.');

        return $this;
    }
}
