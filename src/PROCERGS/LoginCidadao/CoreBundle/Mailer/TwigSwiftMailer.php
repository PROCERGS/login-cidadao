<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Mailer;

use FOS\UserBundle\Mailer\TwigSwiftMailer as BaseMailer;
use FOS\UserBundle\Model\UserInterface;

class TwigSwiftMailer extends BaseMailer
{
    public function sendEmailChangedMessage(UserInterface $user, $oldEmail)
    {
        $template = $this->parameters['template']['email_changed'];

        $context = array(
            'user' => $user,
            'oldEmail' => $oldEmail
        );

        $this->sendMessage($template, $context, $this->parameters['from_email']['email_changed'], $oldEmail);
    }
}
