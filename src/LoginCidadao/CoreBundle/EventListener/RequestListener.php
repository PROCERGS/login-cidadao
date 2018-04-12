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

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

class RequestListener
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * RequestListener constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST === $event->getRequestType()) {
            $this->logReferer($event);
        }
    }

    private function logReferer(GetResponseEvent $event)
    {
        /** @var string|false $referer */
        $referer = $event->getRequest()->headers->get('referer', false);
        if (false === $referer) {
            return;
        }

        $this->logger->info("Request referrer: {$referer}");
    }
}
