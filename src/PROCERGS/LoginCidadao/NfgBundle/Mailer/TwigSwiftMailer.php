<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Mailer;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TwigSwiftMailer implements MailerInterface
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * TwigSwiftMailer constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param UrlGeneratorInterface $router
     * @param \Twig_Environment $twig
     * @param array $parameters
     */
    public function __construct(
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        \Twig_Environment $twig,
        array $parameters
    ) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->parameters = $parameters;
    }

    public function notifyCpfLost(PersonInterface $person)
    {
        // CPF was claimed by another user using NFG
        $template = $this->parameters['template']['cpf_lost'];
        $fromEmail = $this->parameters['email']['address'];
        $fromName = $this->parameters['email']['name'];
        $from = array($fromEmail => $fromName);

        $context = array(
            'person' => $person,
        );

        $this->sendMessage($template, $context, $from, $person->getEmail());
    }

    /**
     * @param string $templateName
     * @param array $context
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $context = $this->twig->mergeGlobals($context);
        $template = $this->twig->loadTemplate($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $this->mailer->send($message);
    }
}
