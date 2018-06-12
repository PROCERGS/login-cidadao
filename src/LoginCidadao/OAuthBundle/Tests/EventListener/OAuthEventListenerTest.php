<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Tests\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Event\OAuthEvent;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\EventListener\OAuthEventListener;
use LoginCidadao\OAuthBundle\Helper\ScopeFinderHelper;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier;
use LoginCidadao\OpenIDBundle\LoginCidadaoOpenIDEvents;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OAuthEventListenerTest extends TestCase
{
    public function testOnPreAuthorizationProcessNotPreAuthorized()
    {
        $person = $this->getPerson();
        $person->expects($this->once())->method('isAuthorizedClient')->willReturn(false);

        $personRepo = $this->getPersonRepository();
        $personRepo->expects($this->once())->method('findOneBy')->willReturn($person);

        $em = $this->getEntityManager(['LoginCidadaoCoreBundle:Person' => $personRepo]);

        $subIdService = $this->getSubjectIdentifierService();

        $listener = new OAuthEventListener($em, $this->getScopeFinder(['openid']), $subIdService);

        $event = new OAuthEvent($person, new Client(), false);
        $listener->onPreAuthorizationProcess($event);

        $this->assertFalse($event->isAuthorizedClient());
    }

    public function testOnPreAuthorizationProcessPreAuthorized()
    {
        $person = $this->getPerson();
        $person->expects($this->once())->method('isAuthorizedClient')->willReturn(true);

        $personRepo = $this->getPersonRepository();
        $personRepo->expects($this->once())->method('findOneBy')->willReturn($person);

        $em = $this->getEntityManager(['LoginCidadaoCoreBundle:Person' => $personRepo]);

        $subIdService = $this->getSubjectIdentifierService();
        $subIdService->expects($this->once())->method('enforceSubjectIdentifier')->willReturn($this->getSubjectIdentifier());

        $listener = new OAuthEventListener($em, $this->getScopeFinder(['openid']), $subIdService);

        $event = new OAuthEvent($person, $this->getClient(), false);
        $listener->onPreAuthorizationProcess($event);

        $this->assertTrue($event->isAuthorizedClient());
    }

    public function testOnPreAuthorizationProcessNoUser()
    {
        $person = $this->getPerson();
        $personRepo = $this->getPersonRepository();
        $personRepo->expects($this->once())->method('findOneBy')->willReturn(null);

        $em = $this->getEntityManager(['LoginCidadaoCoreBundle:Person' => $personRepo]);

        $subIdService = $this->getSubjectIdentifierService();

        $event = new OAuthEvent($person, new Client(), false);

        $listener = new OAuthEventListener($em, $this->getScopeFinder(['openid']), $subIdService);
        $listener->onPreAuthorizationProcess($event);
    }

    public function testOnPreAuthorizationProcessInvalidPersonAndClient()
    {
        $person = $this->createMock('Symfony\Component\Security\Core\User\UserInterface');
        $personRepo = $this->getPersonRepository();
        $personRepo->expects($this->once())->method('findOneBy')->willReturn(null);

        $client = $this->createMock('FOS\OAuthServerBundle\Model\ClientInterface');

        $em = $this->getEntityManager(['LoginCidadaoCoreBundle:Person' => $personRepo]);
        $subIdService = $this->getSubjectIdentifierService();

        $event = new OAuthEvent($person, $client, false);
        $listener = new OAuthEventListener($em, $this->getScopeFinder(['openid']), $subIdService);
        $listener->onPreAuthorizationProcess($event);
        $this->assertFalse($event->isAuthorizedClient());
    }

    public function testOnPostAuthorizationProcessInvalidClient()
    {
        $client = $this->createMock('FOS\OAuthServerBundle\Model\ClientInterface');

        $event = new OAuthEvent(new Person(), $client, true);

        $listener = new OAuthEventListener(
            $this->getEntityManager(),
            $this->getScopeFinder(['openid']),
            $this->getSubjectIdentifierService()
        );

        $listener->onPostAuthorizationProcess($event, OAuthEvent::POST_AUTHORIZATION_PROCESS, $this->getDispatcher());
        $this->assertTrue($event->isAuthorizedClient());
    }

    public function testOnPostAuthorizationProcessNotAuthorized()
    {
        $em = $this->getEntityManager();
        $em->expects($this->never())->method('flush');

        $event = new OAuthEvent(new Person(), new Client(), false);
        $listener = new OAuthEventListener($em, $this->getScopeFinder(['openid']),
            $this->getSubjectIdentifierService());
        $listener->onPostAuthorizationProcess($event, OAuthEvent::POST_AUTHORIZATION_PROCESS, $this->getDispatcher());
    }

    public function testOnPostAuthorizationProcessNewAuth()
    {
        $person = $this->getPerson();
        $personRepo = $this->getPersonRepository();
        $personRepo->expects($this->once())->method('findOneBy')->willReturn($person);

        $authRepo = $this->getAuthorizationRepository();
        $em = $this->getEntityManager([
            'LoginCidadaoCoreBundle:Person' => $personRepo,
            'LoginCidadaoCoreBundle:Authorization' => $authRepo,
        ]);
        $em->expects($this->exactly(2))->method('persist');

        $subIdService = $this->getSubjectIdentifierService();

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(
                LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION_REQUEST,
                $this->isInstanceOf('LoginCidadao\OpenIDBundle\Event\AuthorizationEvent')
            );
        $dispatcher->expects($this->at(1))
            ->method('dispatch')
            ->with(
                LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION,
                $this->isInstanceOf('LoginCidadao\OpenIDBundle\Event\AuthorizationEvent')
            );

        $event = new OAuthEvent(new Person(), $this->getClient(), true);
        $listener = new OAuthEventListener($em, $this->getScopeFinder(['openid']), $subIdService);
        $listener->onPostAuthorizationProcess($event, OAuthEvent::POST_AUTHORIZATION_PROCESS, $dispatcher);

        $this->assertTrue($event->isAuthorizedClient());
    }

    public function testOnPostAuthorizationProcessUpdateAuth()
    {
        $person = $this->getPerson();
        $personRepo = $this->getPersonRepository();
        $personRepo->expects($this->once())->method('findOneBy')->willReturn($person);

        $currentAuth = $this->createMock('LoginCidadao\CoreBundle\Entity\Authorization');

        $authRepo = $this->getAuthorizationRepository();
        $authRepo->expects($this->once())->method('findOneBy')->willReturn($currentAuth);

        $em = $this->getEntityManager([
            'LoginCidadaoCoreBundle:Person' => $personRepo,
            'LoginCidadaoCoreBundle:Authorization' => $authRepo,
        ]);
        $em->expects($this->exactly(2))->method('persist');

        $subIdService = $this->getSubjectIdentifierService();

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(
                LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION_REQUEST,
                $this->isInstanceOf('LoginCidadao\OpenIDBundle\Event\AuthorizationEvent')
            );
        $dispatcher->expects($this->at(1))
            ->method('dispatch')
            ->with(
                LoginCidadaoOpenIDEvents::UPDATE_AUTHORIZATION,
                $this->isInstanceOf('LoginCidadao\OpenIDBundle\Event\AuthorizationEvent')
            );

        $event = new OAuthEvent(new Person(), $this->getClient(), true);
        $listener = new OAuthEventListener($em, $this->getScopeFinder(['openid']), $subIdService);
        $listener->onPostAuthorizationProcess($event, OAuthEvent::POST_AUTHORIZATION_PROCESS, $dispatcher);

        $this->assertTrue($event->isAuthorizedClient());
    }

    /**
     * @param array $repos
     * @return EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager(array $repos = [])
    {
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');

        if (count($repos) > 0) {
            $em->expects($this->atLeastOnce())->method('getRepository')->with($this->isType('string'))
                ->willReturnCallback(function ($key) use ($repos) {
                    return $repos[$key];
                });
        }

        return $em;
    }

    private function getPersonRepository()
    {
        return $this->getMockBuilder('LoginCidadao\CoreBundle\Entity\PersonRepository')
            ->disableOriginalConstructor()->getMock();
    }

    private function getAuthorizationRepository()
    {
        return $this->getMockBuilder('LoginCidadao\CoreBundle\Entity\AuthorizationRepository')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return SubjectIdentifierService|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSubjectIdentifierService()
    {
        return $this->getMockBuilder('LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return PersonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPerson()
    {
        return $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
    }

    /**
     * @param array|null $scope
     * @return ScopeFinderHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getScopeFinder(array $scope = null)
    {
        $helper = $this->getMockBuilder('LoginCidadao\OAuthBundle\Helper\ScopeFinderHelper')
            ->disableOriginalConstructor()->getMock();

        if ($scope) {
            $helper->expects($this->any())->method('getScope')->willReturn($scope);
        }

        return $helper;
    }

    /**
     * @param ClientInterface|null $client
     * @return ClientMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClientMetadata(ClientInterface $client = null)
    {
        $metadata = $this->createMock('LoginCidadao\OpenIDBundle\Entity\ClientMetadata');

        if ($client) {
            $metadata->expects($this->any())->method('getClient')->willReturn($client);
        }

        return $metadata;
    }

    /**
     * @return ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClient()
    {
        $client = $this->createMock('LoginCidadao\OAuthBundle\Model\ClientInterface');

        $client->expects($this->any())->method('getMetadata')->willReturn($this->getClientMetadata($client));

        return $client;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    private function getDispatcher()
    {
        $dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        return $dispatcher;
    }

    /**
     * @return SubjectIdentifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSubjectIdentifier()
    {
        return $this->createMock('LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier');
    }
}
