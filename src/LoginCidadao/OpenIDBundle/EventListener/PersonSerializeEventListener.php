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
use LoginCidadao\APIBundle\Service\VersionService;
use LoginCidadao\OAuthBundle\Model\AccessTokenManager;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;

class PersonSerializeEventListener implements EventSubscriberInterface
{
    /** @var AccessTokenManager */
    private $accessTokenManager;

    /** @var SubjectIdentifierService */
    private $subjectIdentifierService;

    /** @var VersionService */
    private $versionService;

    public function __construct(
        AccessTokenManager $accessTokenManager,
        SubjectIdentifierService $subjectIdentifierService,
        VersionService $versionService
    ) {
        $this->accessTokenManager = $accessTokenManager;
        $this->subjectIdentifierService = $subjectIdentifierService;
        $this->versionService = $versionService;
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
        ];
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
        $client = $this->accessTokenManager->getTokenClient();
        $metadata = $client->getMetadata();

        $sub = $this->subjectIdentifierService->getSubjectIdentifier($event->getObject(), $metadata);

        /** @var GenericSerializationVisitor $visitor */
        $visitor = $event->getVisitor();
        $visitor->addData('sub', $sub);

        $version = $this->getApiVersion();
        if ($version['major'] == 1) {
            $visitor->addData('id', $sub);
        }
    }

    private function addOpenIdConnectCompatibility(ObjectEvent $event)
    {
        /** @var PersonInterface $person */
        $person = $event->getObject();
        /** @var GenericSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        if (version_compare($this->getApiVersion(true), '1.1.0', '>=')) {
            $visitor->addData('picture', $person->getProfilePictureUrl());
            $visitor->addData(
                'email_verified',
                $person->getEmailConfirmedAt() instanceof \DateTime
            );
        }
    }

    /**
     * @param bool $string
     * @return array|string
     */
    private function getApiVersion($string = false)
    {
        $version = $this->versionService->getVersionFromRequest();

        return $string ? $this->versionService->getString($version) : $version;
    }
}
