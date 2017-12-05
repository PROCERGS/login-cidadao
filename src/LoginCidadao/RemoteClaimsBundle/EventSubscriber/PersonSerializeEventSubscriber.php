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
use LoginCidadao\OAuthBundle\Model\AccessTokenManager;
use LoginCidadao\CoreBundle\Model\PersonInterface;
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

    /**
     * PersonSerializeEventSubscriber constructor.
     * @param AccessTokenManager $accessTokenManager
     * @param RemoteClaimManagerInterface $remoteClaimManager
     * @param RemoteClaimFetcherInterface $fetcher
     */
    public function __construct(
        AccessTokenManager $accessTokenManager,
        RemoteClaimManagerInterface $remoteClaimManager,
        RemoteClaimFetcherInterface $fetcher
    ) {
        $this->accessTokenManager = $accessTokenManager;
        $this->remoteClaimManager = $remoteClaimManager;
        $this->fetcher = $fetcher;
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

        $this->addDistributedAndAggregatedClaims($event);
    }

    private function addDistributedAndAggregatedClaims(ObjectEvent $event)
    {
        $visitor = $event->getVisitor();
        if (!$visitor instanceof GenericSerializationVisitor) {
            return;
        }

        $person = $event->getObject();
        if (!$person instanceof PersonInterface) {
            return;
        }
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
            $endpoint = $this->fetcher->discoverClaimUri($claim->getName());

            $claimNames[$name] = $name;
            $claimSources[$name] = [
                'endpoint' => $endpoint,
                'access_token' => $claimAuthorization->getAccessToken(),
            ];
        }

        $visitor->addData('_claim_names', $claimNames);
        $visitor->addData('_claim_sources', $claimSources);
    }
}
