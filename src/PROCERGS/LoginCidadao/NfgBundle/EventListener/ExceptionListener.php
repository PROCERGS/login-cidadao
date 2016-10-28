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

use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
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
        if ($event->getException() instanceof NfgServiceUnavailableException) {
            $url = $this->router->generate('nfg_unavailable', [], RouterInterface::ABSOLUTE_URL);
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
