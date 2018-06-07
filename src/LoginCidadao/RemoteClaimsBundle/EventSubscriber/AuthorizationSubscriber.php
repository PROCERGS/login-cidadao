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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthorizationSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var RemoteClaimFetcherInterface */
    private $claimFetcher;

    /** @var RemoteClaimInterface[] */
    private $remoteClaims;

    /** @var RemoteClaimManagerInterface */
    private $remoteClaimManager;

    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /**
     * AuthorizationSubscriber constructor.
     * @param RemoteClaimManagerInterface $remoteClaimManager
     * @param RemoteClaimFetcherInterface $claimFetcher
     * @param AuthorizationCheckerInterface $authChecker
     */
    public function __construct(
        RemoteClaimManagerInterface $remoteClaimManager,
        RemoteClaimFetcherInterface $claimFetcher,
        AuthorizationCheckerInterface $authChecker
    ) {
        $this->remoteClaimManager = $remoteClaimManager;
        $this->claimFetcher = $claimFetcher;
        $this->authChecker = $authChecker;
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION_REQUEST => 'onNewAuthorizationRequest',
            LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION => ['onNewAuthorization', 100],
            LoginCidadaoOpenIDEvents::UPDATE_AUTHORIZATION => ['onUpdateAuthorization', 100],
            LoginCidadaoOpenIDEvents::REVOKE_AUTHORIZATION => 'onRevokeAuthorization',
        ];
    }

    public function onNewAuthorizationRequest(AuthorizationEvent $event)
    {
        if (false === $this->authChecker->isGranted('FEATURE_REMOTE_CLAIMS')) {
            return;
        }
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
        if (false === $this->authChecker->isGranted('FEATURE_REMOTE_CLAIMS')) {
            return;
        }
        $this->enforceRemoteClaims($event);
    }

    public function onUpdateAuthorization(AuthorizationEvent $event)
    {
        if (false === $this->authChecker->isGranted('FEATURE_REMOTE_CLAIMS')) {
            return;
        }
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
        if (is_array($remoteClaims) && count($remoteClaims) > 0) {
            foreach ($remoteClaims as $remoteClaim) {
                $claimName = $remoteClaim->getName();

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
}
