<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\EventListener;

use FOS\UserBundle\Model\FosUserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestListener
{
    /** @var LoggerInterface */
    private $logger;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var RouterInterface */
    private $router;

    /**
     * RequestListener constructor.
     * @param LoggerInterface $logger
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     */
    public function __construct(LoggerInterface $logger, TokenStorageInterface $tokenStorage, RouterInterface $router)
    {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()) {
            $this->logReferer($event);
            $this->checkUserEnabled($event);
        }
    }

    private function logReferer(GetResponseEvent $event)
    {
        $referer = $event->getRequest()->headers->get('referer', null);
        if (null !== $referer) {
            $this->logger->info("Request referrer: {$referer}");
        }
    }

    private function checkUserEnabled(GetResponseEvent $event)
    {
        if (null !== $token = $this->tokenStorage->getToken()) {
            /** @var FosUserInterface $person */
            if (($person = $token->getUser()) instanceof FosUserInterface && false === $person->isEnabled()) {
                $uri = $this->router->generate('fos_user_security_logout');
                $event->setResponse(new RedirectResponse($uri));
                $event->stopPropagation();
            }
        }
    }
}
