<?php

namespace LoginCidadao\OpenIDBundle\EventListenner;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use PROCERGS\OAuthBundle\Model\AccessTokenManager;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;

class PersonSerializeEventListenner implements EventSubscriberInterface
{
    /** @var AccessTokenManager */
    protected $accessTokenManager;

    /** @var string */
    protected $pairwiseSubjectIdSalt;

    public function __construct(AccessTokenManager $accessTokenManager,
                                $pairwiseSubjectIdSalt)
    {
        $this->accessTokenManager    = $accessTokenManager;
        $this->pairwiseSubjectIdSalt = $pairwiseSubjectIdSalt;
    }

    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'class' => 'PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface'
            )
        );
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        if (!($event->getObject() instanceof PersonInterface)) {
            return;
        }
        $this->setSubjectIdentifier($event);
        $this->addOpenIdConnectCompatibility($event);
    }

    private function setSubjectIdentifier(ObjectEvent $event)
    {
        $client   = $this->accessTokenManager->getTokenClient();
        $metadata = $client->getMetadata();

        $id = $event->getObject()->getId();
        if ($metadata === null || $metadata->getSubjectType() === 'public') {
            $event->getVisitor()->addData('sub', $id);
            $event->getVisitor()->addData('id', $id);
            return;
        }

        if ($metadata->getSubjectType() === 'pairwise') {
            $sectorIdentifier = $metadata->getSectorIdentifier();
            $salt             = $this->pairwiseSubjectIdSalt;
            $pairwise         = hash('sha256', $sectorIdentifier.$id.$salt);
            $event->getVisitor()->addData('sub', $pairwise);
            $event->getVisitor()->addData('id', $pairwise);
        }
    }

    private function addOpenIdConnectCompatibility(ObjectEvent $event)
    {
        $person  = $event->getObject();
        $visitor = $event->getVisitor();

        if (!($person instanceof PersonInterface)) {
            return;
        }

        $visitor->addData('picture', $person->getProfilePictureUrl());
        $visitor->addData('email_verified',
            $person->getEmailConfirmedAt() instanceof \DateTime);
    }
}
