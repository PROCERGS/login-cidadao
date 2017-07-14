<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\EventListener;

use PROCERGS\LoginCidadao\NfgBundle\Exception\ConnectionNotFoundException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\CpfMismatchException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\EmailInUseException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\MissingRequiredInformationException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgAccountCollisionException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ExceptionListener
{
    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * ExceptionListener constructor.
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     */
    public function __construct(RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $e = $event->getException();
        if ($e instanceof NfgServiceUnavailableException) {
            $url = $this->router->generate('nfg_unavailable', [], RouterInterface::ABSOLUTE_URL);
            $event->setResponse(new RedirectResponse($url));

            return;
        }

        if ($e instanceof EmailInUseException) {
            $url = $this->router->generate('nfg_help_email_in_use', [], RouterInterface::ABSOLUTE_URL);
            $event->setResponse(new RedirectResponse($url));

            return;
        }

        if ($e instanceof NfgAccountCollisionException) {
            // Both users are linked to the same NFG account
            $url = $this->router->generate(
                'nfg_help_already_connected',
                ['access_token' => $e->getAccessToken()],
                RouterInterface::ABSOLUTE_URL
            );
            $event->setResponse(new RedirectResponse($url));

            return;
        }

        if ($e instanceof MissingRequiredInformationException) {
            $url = $this->router->generate('nfg_missing_info', [], RouterInterface::ABSOLUTE_URL);
            $event->setResponse(new RedirectResponse($url));

            return;
        }

        if ($e instanceof CpfMismatchException) {
            $url = $this->router->generate('nfg_help_cpf_did_not_match', [], RouterInterface::ABSOLUTE_URL);
            $event->setResponse(new RedirectResponse($url));

            return;
        }

        if ($e instanceof ConnectionNotFoundException) {
            $session = $event->getRequest()->getSession();
            if ($session instanceof Session) {
                $msg = $this->translator->trans(
                    'nfg.connection_not_found.alert',
                    [
                        '%help_url%' => $this->router->generate('nfg_help_connection_not_found'),
                    ]
                );
                $session->getFlashBag()->add('danger', $msg);
            }
            $url = $this->router->generate('fos_user_security_login', [], RouterInterface::ABSOLUTE_URL);
            $event->setResponse(new RedirectResponse($url));

            return;
        }
    }
}
