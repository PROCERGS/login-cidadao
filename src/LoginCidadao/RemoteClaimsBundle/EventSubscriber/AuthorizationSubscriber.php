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

use LoginCidadao\OpenIDBundle\Event\AuthorizationEvent;
use LoginCidadao\OpenIDBundle\LoginCidadaoOpenIDEvents;
use LoginCidadao\RemoteClaimsBundle\Model\HttpUri;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthorizationSubscriber implements EventSubscriberInterface
{
    /** @var RemoteClaimFetcherInterface */
    private $claimFetcher;

    /** @var RemoteClaimInterface[] */
    private $remoteClaims;

    /**
     * AuthorizationSubscriber constructor.
     * @param RemoteClaimFetcherInterface $claimFetcher
     */
    public function __construct(RemoteClaimFetcherInterface $claimFetcher)
    {
        $this->claimFetcher = $claimFetcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION_REQUEST => 'onNewAuthorizationRequest',
        ];
    }

    public function onNewAuthorizationRequest(AuthorizationEvent $event)
    {
        foreach ($event->getScope() as $scope) {
            if ($this->checkHttpUri($scope) || $this->checkTagUri($scope)) {
                $remoteClaim = $this->claimFetcher->getRemoteClaim($scope);
                $this->remoteClaims[] = $remoteClaim;
                $event->addRemoteClaim($remoteClaim);
            }
        }
    }

    private function checkHttpUri($uri)
    {
        try {
            $http = HttpUri::createFromString($uri);

            return $http->getScheme() && $http->getHost();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkTagUri($uri)
    {
        try {
            TagUri::createFromString($uri);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
