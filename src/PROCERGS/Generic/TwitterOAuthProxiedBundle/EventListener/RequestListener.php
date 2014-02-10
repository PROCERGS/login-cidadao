<?php

namespace PROCERGS\Generic\TwitterOAuthProxiedBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use PROCERGS\Generic\TwitterOAuthProxiedBundle\Service\TwitterOAuth;

class RequestListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        try {
            $proxy = $this->container->getParameter('http_proxy');
            TwitterOAuth::setProxy($proxy);
        } catch (Exception $e) {

        }
    }
}
