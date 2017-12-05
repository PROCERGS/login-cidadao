<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\EventListener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\GenericSerializationVisitor;
use LoginCidadao\OAuthBundle\Model\AccessTokenManager;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;

class PersonSerializeEventListener implements EventSubscriberInterface
{
    /** @var AccessTokenManager */
    protected $accessTokenManager;

    /** @var SubjectIdentifierService */
    protected $subjectIdentifierService;

    public function __construct(
        AccessTokenManager $accessTokenManager,
        SubjectIdentifierService $subjectIdentifierService
    ) {
        $this->accessTokenManager = $accessTokenManager;
        $this->subjectIdentifierService = $subjectIdentifierService;
    }

    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ),
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
        $visitor = $event->getVisitor();
        if (!$visitor instanceof GenericSerializationVisitor) {
            return;
        }
        $client = $this->accessTokenManager->getTokenClient();
        $metadata = $client->getMetadata();

        $sub = $this->subjectIdentifierService->getSubjectIdentifier($event->getObject(), $metadata);
        $visitor->addData('sub', $sub);
        $visitor->addData('id', $sub);
    }

    private function addOpenIdConnectCompatibility(ObjectEvent $event)
    {
        $visitor = $event->getVisitor();
        if (!$visitor instanceof GenericSerializationVisitor) {
            return;
        }

        $person = $event->getObject();

        if (!($person instanceof PersonInterface)) {
            return;
        }

        $visitor->addData('picture', $person->getProfilePictureUrl());
        $visitor->addData(
            'email_verified',
            $person->getEmailConfirmedAt() instanceof \DateTime
        );
    }
}
