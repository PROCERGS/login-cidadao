<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Mailer;

use FOS\UserBundle\Mailer\TwigSwiftMailer;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;

/**
 * Class AccountRecoveryMailer
 * @package LoginCidadao\AccountRecoveryBundle\Mailer
 * @codeCoverageIgnore Twig is too troublesome to test...
 */
class AccountRecoveryMailer extends TwigSwiftMailer
{
    private const TEMPLATE_EMAIL_CHANGED = 'LoginCidadaoAccountRecoveryBundle:Email:email_changed.html.twig';
    private const TEMPLATE_EMAIL_REMOVED = 'LoginCidadaoAccountRecoveryBundle:Email:email_removed.html.twig';

    private const TEMPLATE_PHONE_CHANGED = 'LoginCidadaoAccountRecoveryBundle:Email:phone_changed.html.twig';
    private const TEMPLATE_PHONE_REMOVED = 'LoginCidadaoAccountRecoveryBundle:Email:phone_removed.html.twig';

    public function sendRecoveryEmailChangedMessage(AccountRecoveryData $accountRecoveryData, string $toEmail)
    {
        $this->sendRecoveryDataChangedMessage(
            self::TEMPLATE_EMAIL_CHANGED,
            $accountRecoveryData,
            $toEmail,
            ['newEmail' => $accountRecoveryData->getEmail()]
        );
    }

    public function sendRecoveryEmailRemovedMessage(AccountRecoveryData $accountRecoveryData, string $toEmail)
    {
        $this->sendRecoveryDataChangedMessage(
            self::TEMPLATE_EMAIL_REMOVED,
            $accountRecoveryData,
            $toEmail
        );
    }

    public function sendRecoveryPhoneChangedMessage(AccountRecoveryData $accountRecoveryData, string $toEmail)
    {
        $this->sendRecoveryDataChangedMessage(
            self::TEMPLATE_PHONE_CHANGED,
            $accountRecoveryData,
            $toEmail,
            ['newPhone' => $accountRecoveryData->getMobile()]
        );
    }

    public function sendRecoveryPhoneRemovedMessage(AccountRecoveryData $accountRecoveryData, string $toEmail)
    {
        $this->sendRecoveryDataChangedMessage(
            self::TEMPLATE_PHONE_REMOVED,
            $accountRecoveryData,
            $toEmail
        );
    }

    private function sendRecoveryDataChangedMessage(
        string $template,
        AccountRecoveryData $accountRecoveryData,
        string $toEmail,
        array $context = []
    ) {
        $person = $accountRecoveryData->getPerson();
        $context['name'] = $person->getFirstName() ?? $person->getEmail();

        $fromEmail = $this->parameters['from_email']['recovery_data_changed'];
        $fromName = $this->parameters['from_email']['email_sender_name'];
        $from = [$fromEmail => $fromName];

        $this->sendMessage($template, $context, $from, $toEmail);
    }
}
