<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\EventSubscriber;

use LoginCidadao\LogBundle\Traits\LoggerAwareTrait;
use LoginCidadao\RemoteClaimsBundle\Event\UpdateRemoteClaimUriEvent;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManagerInterface;
use LoginCidadao\RemoteClaimsBundle\RemoteClaimEvents;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoteClaimSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var RemoteClaimManagerInterface */
    private $remoteClaimManager;

    /**
     * AuthorizationSubscriber constructor.
     * @param RemoteClaimManagerInterface $remoteClaimManager
     */
    public function __construct(RemoteClaimManagerInterface $remoteClaimManager)
    {
        $this->remoteClaimManager = $remoteClaimManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            RemoteClaimEvents::REMOTE_CLAIM_UPDATE_URI => 'onRemoteClaimUriUpdate',
        ];
    }

    public function onRemoteClaimUriUpdate(UpdateRemoteClaimUriEvent $event)
    {
        $this->remoteClaimManager->updateRemoteClaimUri($event->getClaimName(), $event->getNewUri());
    }
}
