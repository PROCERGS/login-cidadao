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
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\EventListener\OAuthEventListener;
use LoginCidadao\OpenIDBundle\LoginCidadaoOpenIDEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class OAuthEventListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnPreAuthorizationProcessNoAuthorization()
    {
        $person = new Person();
        $client = new Client();
        $isAuthorized = false;

        $event = new OAuthEvent($person, $client, $isAuthorized);
        $listener = $this->getListener(null, null, $this->getRequest(['scope' => 'scope1']));
        $listener->onPreAuthorizationProcess($event);

        $this->assertFalse($event->isAuthorizedClient());
    }

    public function testOnPreAuthorizationProcessWithAuthorization()
    {
        $person = new Person();
        $client = new Client();

        $scope = 'scope1';
        $authorization = new Authorization();
        $authorization->setPerson($person);
        $authorization->setClient($client);
        $authorization->setScope($scope);

        $person->addAuthorization($authorization);

        $isAuthorized = false;

        $event = new OAuthEvent($person, $client, $isAuthorized);
        $listener = $this->getListener(null, null, $this->getRequest(['scope' => $scope]));
        $listener->onPreAuthorizationProcess($event);

        $this->assertTrue($event->isAuthorizedClient());
    }

    public function testOnPreAuthorizationProcessInvalidPersonAndClient()
    {
        $person = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $client = $this->getMock('FOS\OAuthServerBundle\Model\ClientInterface');
        $isAuthorized = false;

        $event = new OAuthEvent($person, $client, $isAuthorized);
        $listener = $this->getListener(null, null, $this->getRequest(['scope' => 'scope1']));
        $listener->onPreAuthorizationProcess($event);

        $this->assertFalse($event->isAuthorizedClient());
    }

    public function testOnPostAuthorizationProcessNoClient()
    {
        $scope = 'scope1';
        $person = new Person();
        $client = $this->getMock('FOS\OAuthServerBundle\Model\ClientInterface');
        $isAuthorized = true;

        $event = new OAuthEvent($person, $client, $isAuthorized);
        $listener = $this->getListener(null, null, $this->getRequest(['scope' => $scope]));
        $listener->onPostAuthorizationProcess($event, OAuthEvent::POST_AUTHORIZATION_PROCESS, $this->getDispatcher());

        $this->assertTrue($event->isAuthorizedClient());
    }

    public function testOnPostAuthorizationProcessNoAuthorization()
    {
        $person = new Person();
        $client = new Client();
        $isAuthorized = false;

        $event = new OAuthEvent($person, $client, $isAuthorized);
        $listener = $this->getListener();
        $listener->onPostAuthorizationProcess($event, OAuthEvent::POST_AUTHORIZATION_PROCESS, $this->getDispatcher());

        $this->assertFalse($event->isAuthorizedClient());
    }

    public function testOnPostAuthorizationProcessUpdateAuthorization()
    {
        $person = new Person();
        $client = new Client();
        $isAuthorized = true;

        $currentAuth = new Authorization();

        $authRepo = $this->getMockBuilder('LoginCidadao\CoreBundle\Entity\AuthorizationRepository')
            ->disableOriginalConstructor()->getMock();
        $authRepo->expects($this->once())->method('findOneBy')->willReturn($currentAuth);

        $em = $this->getEntityManager();
        $em->expects($this->once())
            ->method('getRepository')
            ->with('LoginCidadaoCoreBundle:Authorization')
            ->willReturn($authRepo);
        $em->expects($this->once())
            ->method('persist');
        $em->expects($this->once())
            ->method('flush');

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                LoginCidadaoOpenIDEvents::UPDATE_AUTHORIZATION,
                $this->isInstanceOf('LoginCidadao\OpenIDBundle\Event\AuthorizationEvent')
            );

        $event = new OAuthEvent($person, $client, $isAuthorized);
        $listener = $this->getListener($em);
        $listener->onPostAuthorizationProcess($event, OAuthEvent::POST_AUTHORIZATION_PROCESS, $dispatcher);

        $this->assertTrue($event->isAuthorizedClient());
    }

    public function testOnPostAuthorizationProcessNewAuthorization()
    {
        $person = new Person();
        $client = new Client();
        $isAuthorized = true;

        $authRepo = $this->getMockBuilder('LoginCidadao\CoreBundle\Entity\AuthorizationRepository')
            ->disableOriginalConstructor()->getMock();
        $authRepo->expects($this->once())->method('findOneBy')->willReturn(null);

        $em = $this->getEntityManager();
        $em->expects($this->once())
            ->method('getRepository')
            ->with('LoginCidadaoCoreBundle:Authorization')
            ->willReturn($authRepo);
        $em->expects($this->once())
            ->method('persist');
        $em->expects($this->once())
            ->method('flush');

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION,
                $this->isInstanceOf('LoginCidadao\OpenIDBundle\Event\AuthorizationEvent')
            );

        $event = new OAuthEvent($person, $client, $isAuthorized);
        $listener = $this->getListener($em);
        $listener->onPostAuthorizationProcess($event, OAuthEvent::POST_AUTHORIZATION_PROCESS, $dispatcher);

        $this->assertTrue($event->isAuthorizedClient());
    }

    private function getListener($em = null, $form = null, $request = null)
    {
        if (!$em) {
            $em = $this->getEntityManager();
        }
        if (!$form) {
            $form = $this->getForm();
        }
        if (!$request) {
            $request = $this->getRequest();
        }

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $listener = new OAuthEventListener($em, $form, $requestStack);

        return $listener;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private function getEntityManager()
    {
        return $this->getMock('Doctrine\ORM\EntityManagerInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FormInterface
     */
    private function getForm()
    {
        return $this->getMock('Symfony\Component\Form\FormInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Request
     */
    private function getRequest($query = [], $request = [])
    {
        $request = new Request($query, $request);

        return $request;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    private function getDispatcher()
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        return $dispatcher;
    }
}
