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

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\GenericSerializationVisitor;
use LoginCidadao\APIBundle\Service\VersionService;
use LoginCidadao\OAuthBundle\Model\AccessTokenManager;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\RemoteClaimsBundle\Exception\ClaimUriUnavailableException;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimAuthorizationInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManagerInterface;

class PersonSerializeEventSubscriber implements EventSubscriberInterface
{
    /** @var RemoteClaimManagerInterface */
    private $remoteClaimManager;

    /** @var AccessTokenManager */
    private $accessTokenManager;

    /** @var RemoteClaimFetcherInterface */
    private $fetcher;

    /** @var VersionService */
    private $versionService;

    /**
     * PersonSerializeEventSubscriber constructor.
     * @param AccessTokenManager $accessTokenManager
     * @param RemoteClaimManagerInterface $remoteClaimManager
     * @param RemoteClaimFetcherInterface $fetcher
     * @param VersionService $versionService
     */
    public function __construct(
        AccessTokenManager $accessTokenManager,
        RemoteClaimManagerInterface $remoteClaimManager,
        RemoteClaimFetcherInterface $fetcher,
        VersionService $versionService
    ) {
        $this->accessTokenManager = $accessTokenManager;
        $this->remoteClaimManager = $remoteClaimManager;
        $this->fetcher = $fetcher;
        $this->versionService = $versionService;
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'method' => 'onPostSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        if (!$event->getObject() instanceof PersonInterface) {
            return;
        }

        if ($this->checkVersion()) {
            $this->addDistributedAndAggregatedClaims($event);
        }
    }

    private function addDistributedAndAggregatedClaims(ObjectEvent $event)
    {
        $visitor = $event->getVisitor();
        if (!$visitor instanceof GenericSerializationVisitor) {
            return;
        }

        $person = $event->getObject();
        $client = $this->accessTokenManager->getTokenClient();

        $remoteClaims = $this->remoteClaimManager->getRemoteClaimsWithTokens($client, $person);

        $claimNames = [];
        $claimSources = [];
        foreach ($remoteClaims as $remoteClaim) {
            /** @var RemoteClaimInterface $claim */
            $claim = $remoteClaim['remoteClaim'];

            /** @var RemoteClaimAuthorizationInterface $claimAuthorization */
            $claimAuthorization = $remoteClaim['authorization'];

            $name = (string)$claim->getName();
            try {
                $endpoint = $this->fetcher->discoverClaimUri($claim->getName());
            } catch (ClaimUriUnavailableException $e) {
                // TODO: log error
                continue;
            }

            $claimNames[$name] = $name;
            $claimSources[$name] = [
                'endpoint' => $endpoint,
                'access_token' => $claimAuthorization->getAccessToken(),
            ];
        }

        $visitor->setData('_claim_names', $claimNames);
        $visitor->setData('_claim_sources', $claimSources);
    }

    /**
     * @return bool
     */
    private function checkVersion()
    {
        $version = $this->versionService->getVersionFromRequest();
        $version = $this->versionService->getString($version);

        return version_compare($version, '2.0.0', '>=');
    }
}
