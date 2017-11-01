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
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\EventListener\OAuthEventListener;
use LoginCidadao\OAuthBundle\Helper\ScopeFinderHelper;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;

class OAuthEventListenerTest extends \PHPUnit_Framework_TestCase
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
    }

    public function testOnPreAuthorizationProcessPreAuthorizedSubNotPersisted()
    {
        $sub = 'abc123';

        $person = $this->getPerson();
        $person->expects($this->once())->method('isAuthorizedClient')->willReturn(true);

        $personRepo = $this->getPersonRepository();
        $personRepo->expects($this->once())->method('findOneBy')->willReturn($person);

        $em = $this->getEntityManager(['LoginCidadaoCoreBundle:Person' => $personRepo]);
        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf('LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier'));

        $subIdService = $this->getSubjectIdentifierService();
        $subIdService->expects($this->once())->method('isSubjectIdentifierPersisted')->willReturn(false);
        $subIdService->expects($this->once())->method('getSubjectIdentifier')->willReturn($sub);

        $listener = new OAuthEventListener($em, $this->getScopeFinder(['openid']), $subIdService);

        $event = new OAuthEvent($person, new Client(), false);
        $listener->onPreAuthorizationProcess($event);
    }

    public function testOnPreAuthorizationProcessPreAuthorizedSubPersisted()
    {
        $person = $this->getPerson();
        $person->expects($this->once())->method('isAuthorizedClient')->willReturn(true);

        $personRepo = $this->getPersonRepository();
        $personRepo->expects($this->once())->method('findOneBy')->willReturn($person);

        $em = $this->getEntityManager(['LoginCidadaoCoreBundle:Person' => $personRepo]);

        $subIdService = $this->getSubjectIdentifierService();
        $subIdService->expects($this->once())->method('isSubjectIdentifierPersisted')->willReturn(true);

        $listener = new OAuthEventListener($em, $this->getScopeFinder(['openid']), $subIdService);

        $event = new OAuthEvent($person, new Client(), false);
        $listener->onPreAuthorizationProcess($event);
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

    public function testOnPostAuthorizationProcessNotAuthorized()
    {
        $event = new OAuthEvent(new Person(), new Client(), false);
        $listener = new OAuthEventListener($this->getEntityManager(), $this->getScopeFinder(['openid']),
            $this->getSubjectIdentifierService());
        $listener->onPostAuthorizationProcess($event);
    }

    public function testOnPostAuthorizationProcessNewAuth()
    {
        $sub = 'abc123';

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
        $subIdService->expects($this->once())->method('getSubjectIdentifier')->willReturn($sub);

        $event = new OAuthEvent(new Person(), new Client(), true);
        $listener = new OAuthEventListener($em, $this->getScopeFinder(['openid']), $subIdService);
        $listener->onPostAuthorizationProcess($event);
    }

    public function testOnPostAuthorizationProcessMergeAuth()
    {
        $client = new Client();
        $person = $this->getPerson();
        $personRepo = $this->getPersonRepository();
        $personRepo->expects($this->once())->method('findOneBy')->willReturn($person);

        $auth = new Authorization();
        $auth->setPerson($person);
        $auth->setClient($client);
        $auth->setScope(['scope1', 'scope2']);

        $authRepo = $this->getAuthorizationRepository();
        $authRepo->expects($this->once())->method('findOneBy')->willReturn($auth);

        $em = $this->getEntityManager([
            'LoginCidadaoCoreBundle:Person' => $personRepo,
            'LoginCidadaoCoreBundle:Authorization' => $authRepo,
        ]);

        $subIdService = $this->getSubjectIdentifierService();
        $subIdService->expects($this->once())->method('isSubjectIdentifierPersisted')->willReturn(true);

        $event = new OAuthEvent($person, $client, true);
        $listener = new OAuthEventListener($em, $this->getScopeFinder(['scope1', 'scope2', 'scope3']), $subIdService);
        $listener->onPostAuthorizationProcess($event);

        $this->assertContains('scope3', $auth->getScope());
    }

    /**
     * @param array $repos
     * @return EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager(array $repos = [])
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

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
        return $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
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
}
