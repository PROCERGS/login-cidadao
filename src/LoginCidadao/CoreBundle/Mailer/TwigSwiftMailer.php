<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Mailer;

use FOS\UserBundle\Mailer\TwigSwiftMailer as BaseMailer;
use FOS\UserBundle\Model\UserInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class TwigSwiftMailer extends BaseMailer
{
    protected $mailerSenderMail;

    public function setMailerSenderMail($var)
    {
        $this->mailerSenderMail = $var;
    }

    public function sendEmailChangedMessage(UserInterface $user, $oldEmail)
    {
        $template = $this->parameters['template']['email_changed'];
        $fromEmail = $this->parameters['from_email']['email_changed'];
        $fromName = $this->parameters['from_email']['email_sender_name'];
        $from = [$fromEmail => $fromName];

        $context = [
            'user' => $user,
            'oldEmail' => $oldEmail,
        ];

        $this->sendMessage($template, $context, $from, $oldEmail);
    }

    public function sendAccountBlockedMessage(PersonInterface $person)
    {
        $template = $this->parameters['template']['account_blocked'];
        $fromEmail = $this->parameters['from_email']['account_blocked'];
        $fromName = $this->parameters['from_email']['email_sender_name'];
        $from = [$fromEmail => $fromName];

        $context = ['email' => $person->getEmail()];
        $this->sendMessage($template, $context, $from, $person->getEmail());
    }

    public function sendAccountAutoBlockedMessage(PersonInterface $person)
    {
        $template = $this->parameters['template']['account_auto_blocked'];
        $fromEmail = $this->parameters['from_email']['account_auto_blocked'];
        $fromName = $this->parameters['from_email']['email_sender_name'];
        $from = [$fromEmail => $fromName];

        $context = ['email' => $person->getEmail()];
        $this->sendMessage($template, $context, $from, $person->getEmail());
    }
}
