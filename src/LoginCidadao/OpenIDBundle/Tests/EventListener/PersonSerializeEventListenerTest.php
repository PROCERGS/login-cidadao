<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\EventListener;

use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\GenericSerializationVisitor;
use LoginCidadao\APIBundle\Service\VersionService;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Model\AccessTokenManager;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\EventListener\PersonSerializeEventListener;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;

class PersonSerializeEventListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            [
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
        ], PersonSerializeEventListener::getSubscribedEvents());
    }

    public function testOnPostSerializeNonPerson()
    {
        $object = new \stdClass();

        /** @var \PHPUnit_Framework_MockObject_MockObject|ObjectEvent $event */
        $event = $this->getMockBuilder('JMS\Serializer\EventDispatcher\ObjectEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getObject')->willReturn($object);
        $event->expects($this->never())->method('getVisitor');


        $listener = new PersonSerializeEventListener(
            $this->getAccessTokenManager(),
            $this->getSubjectIdentifierService(),
            $this->getVersionService()
        );
        $listener->onPostSerialize($event);
    }

    public function testOnPostSerializePersonV2()
    {
        $this->runOnPostSerializeTest(['major' => 2, 0, 0], true, 3);
    }

    public function testOnPostSerializePersonV1()
    {
        $this->runOnPostSerializeTest(['major' => 1, 0, 0], false, 2);
        $this->runOnPostSerializeTest(['major' => 1, 1, 0], true, 4);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AccessTokenManager
     */
    private function getAccessTokenManager()
    {
        return $this->getMockBuilder('LoginCidadao\OAuthBundle\Model\AccessTokenManager')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SubjectIdentifierService
     */
    private function getSubjectIdentifierService()
    {
        return $this->getMockBuilder('LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|VersionService
     */
    private function getVersionService()
    {
        return $this->getMockBuilder('LoginCidadao\APIBundle\Service\VersionService')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GenericSerializationVisitor
     */
    private function getVisitor()
    {
        return $this->getMockBuilder('JMS\Serializer\GenericSerializationVisitor')
            ->disableOriginalConstructor()->getMock();
    }

    private function getAddDataCallback($sub, $pictureUrl)
    {
        return function ($key, $value) use ($sub, $pictureUrl) {
            switch ($key) {
                case 'id':
                case 'sub':
                    $this->assertEquals($sub, $value);
                    break;
                case 'picture':
                    $this->assertEquals($pictureUrl, $value);
                    break;
                case 'email_verified':
                    $this->assertTrue($value);
                    break;
                default:
                    $this->fail("Unexpected addData call: {$key}");
            }
        };
    }

    private function runOnPostSerializeTest($version, $expectOIDCFields, $addDataCount)
    {
        $pictureUrl = 'https://picture.url/pic.jpg';
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        if ($expectOIDCFields) {
            $person->expects($this->once())->method('getProfilePictureUrl')->willReturn($pictureUrl);
            $person->expects($this->once())->method('getEmailConfirmedAt')->willReturn(new \DateTime());
        }

        $visitor = $this->getVisitor();
        $visitor->expects($this->exactly($addDataCount))
            ->method('addData')
            ->willReturnCallback($this->getAddDataCallback('sub', $pictureUrl));

        /** @var \PHPUnit_Framework_MockObject_MockObject|ObjectEvent $event */
        $event = $this->getMockBuilder('JMS\Serializer\EventDispatcher\ObjectEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->exactly(3))->method('getObject')->willReturn($person);
        $event->expects($this->exactly(2))->method('getVisitor')->willReturn($visitor);

        $client = new Client();
        $metadata = new ClientMetadata();
        $client->setMetadata($metadata);
        $metadata->setClient($client);

        $accessTokenManager = $this->getAccessTokenManager();
        $accessTokenManager->expects($this->once())
            ->method('getTokenClient')
            ->willReturn($client);

        $subjectIdentifierService = $this->getSubjectIdentifierService();
        $subjectIdentifierService->expects($this->once())
            ->method('getSubjectIdentifier')
            ->with($person, $metadata)
            ->willReturn('sub');

        $versionService = $this->getVersionService();
        $versionService->expects($this->exactly(2))->method('getVersionFromRequest')->willReturn($version);
        $versionService->expects($this->once())->method('getString')->willReturn(implode('.', $version));

        $listener = new PersonSerializeEventListener(
            $accessTokenManager,
            $subjectIdentifierService,
            $versionService
        );
        $listener->onPostSerialize($event);
    }
}
