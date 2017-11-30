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
use LoginCidadao\OpenIDBundle\Event\AuthorizationEvent;
use LoginCidadao\OpenIDBundle\LoginCidadaoOpenIDEvents;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorization;
use LoginCidadao\RemoteClaimsBundle\Model\HttpUri;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManagerInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthorizationSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var RemoteClaimFetcherInterface */
    private $claimFetcher;

    /** @var RemoteClaimInterface[] */
    private $remoteClaims;

    /** @var RemoteClaimManagerInterface */
    private $remoteClaimManager;

    /**
     * AuthorizationSubscriber constructor.
     * @param RemoteClaimManagerInterface $remoteClaimManager
     * @param RemoteClaimFetcherInterface $claimFetcher
     */
    public function __construct(
        RemoteClaimManagerInterface $remoteClaimManager,
        RemoteClaimFetcherInterface $claimFetcher
    ) {
        $this->remoteClaimManager = $remoteClaimManager;
        $this->claimFetcher = $claimFetcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION_REQUEST => 'onNewAuthorizationRequest',
            LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION => 'onNewAuthorization',
            LoginCidadaoOpenIDEvents::UPDATE_AUTHORIZATION => 'onUpdateAuthorization',
            LoginCidadaoOpenIDEvents::REVOKE_AUTHORIZATION => 'onRevokeAuthorization',
        ];
    }

    public function onNewAuthorizationRequest(AuthorizationEvent $event)
    {
        foreach ($event->getScope() as $scope) {
            if ($this->checkHttpUri($scope) || $this->checkTagUri($scope)) {
                try {
                    $remoteClaim = $this->claimFetcher->getRemoteClaim($scope);
                    $this->remoteClaims[] = $remoteClaim;
                    $event->addRemoteClaim($remoteClaim);
                } catch (\Exception $e) {
                    $this->error(
                        "Error retrieving remote claim {$scope}: {$e->getMessage()}",
                        ['exception' => $e]
                    );
                }
            }
        }
    }

    public function onNewAuthorization(AuthorizationEvent $event)
    {
        $this->enforceRemoteClaims($event);
    }

    public function onUpdateAuthorization(AuthorizationEvent $event)
    {
        $this->enforceRemoteClaims($event);
    }

    public function onRevokeAuthorization(AuthorizationEvent $event)
    {
        if (count($event->getRemoteClaims()) === 0) {
            return;
        }

        $this->remoteClaimManager->revokeAllAuthorizations($event->getAuthorization());
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

    private function enforceRemoteClaims(AuthorizationEvent $event)
    {
        $remoteClaims = $event->getRemoteClaims();

        foreach ($remoteClaims as $remoteClaim) {
            $claimName = $remoteClaim->getName();
            if (is_string($claimName)) {
                $claimName = TagUri::createFromString($claimName);
            }

            $accessToken = bin2hex(random_bytes(20));
            $authorization = (new RemoteClaimAuthorization())
                ->setClient($event->getClient())
                ->setClaimProvider($remoteClaim->getProvider())
                ->setPerson($event->getPerson())
                ->setClaimName($claimName)
                ->setAccessToken($accessToken);

            $this->remoteClaimManager->enforceAuthorization($authorization);
        }
    }
}
