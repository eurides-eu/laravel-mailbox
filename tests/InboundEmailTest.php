<?php

namespace BeyondCode\Mailbox\Tests;

use BeyondCode\Mailbox\Facades\Mailbox;
use BeyondCode\Mailbox\InboundEmail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class InboundEmailTest extends TestCase
{

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']['mail.driver'] = 'log';
        $app['config']['mailbox.driver'] = 'log';
    }

    /** @test */
    public function it_stores_inbound_emails()
    {
        Mailbox::from('example@beyondco.de', function($email) {
        });

        Mail::to('someone@beyondco.de')->send(new TestMail);
        Mail::to('someone@beyondco.de')->send(new TestMail);

        $this->assertSame(2, InboundEmail::query()->count());
    }

    /** @test */
    public function it_does_not_store_inbound_emails_if_configured()
    {
        $this->app['config']['mailbox.store_incoming_emails_for_days'] = 0;

        Mailbox::from('example@beyondco.de', function($email) {
        });

        Mail::to('someone@beyondco.de')->send(new TestMail);
        Mail::to('someone@beyondco.de')->send(new TestMail);

        $this->assertSame(0, InboundEmail::query()->count());
    }

    /** @test */
    public function it_can_reply_to_mails()
    {

        Mailbox::from('example@beyondco.de', function(InboundEmail $email) {
            Mail::fake();

            $email->reply(new ReplyMail);
        });

        Mail::to('someone@beyondco.de')->send(new TestMail);

        Mail::assertSent(ReplyMail::class);
    }

    /** @test */
    public function it_can_forward_mails()
    {

        Mailbox::from('example@beyondco.de', function(InboundEmail $email) {
            $email->forward('forward@beyondco.de');
        });

        Mail::to('someone@beyondco.de')->send(new TestMail);
    }

}

class TestMail extends Mailable
{
    public function build()
    {
        $this->from('example@beyondco.de')
            ->subject('This is a subject')
            ->html('<html>Example email content</html>');
    }
}

class ReplyMail extends Mailable
{
    public function build()
    {
        $this->from('marcel@beyondco.de')
            ->subject('This is my reply')
            ->html('Hi!');
    }
}