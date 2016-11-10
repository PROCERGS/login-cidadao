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
use PROCERGS\LoginCidadao\NfgBundle\Exception\MissingRequiredInformationException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgAccountCollisionException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;

class ExceptionListener
{
    /** @var RouterInterface */
    private $router;

    /**
     * ExceptionListener constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
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

        if ($e instanceof NfgAccountCollisionException) {
            // Both users are linked to the same NFG account
            // TODO:
            return;
        }

        if ($e instanceof MissingRequiredInformationException) {
            $url = $this->router->generate('nfg_missing_info', [], RouterInterface::ABSOLUTE_URL);
            $event->setResponse(new RedirectResponse($url));

            return;
        }

        if ($e instanceof CpfMismatchException) {
            $url = $this->router->generate('lc_documents', [], RouterInterface::ABSOLUTE_URL);
            $event->setResponse(new RedirectResponse($url));

            return;
        }

        if ($e instanceof ConnectionNotFoundException) {
            $session = $event->getRequest()->getSession();
            if ($session instanceof Session) {
                $session->getFlashBag()->add('error', $e->getMessage());
            }
            $url = $this->router->generate('fos_user_security_login', [], RouterInterface::ABSOLUTE_URL);
            $event->setResponse(new RedirectResponse($url));

            return;
        }
    }
}
