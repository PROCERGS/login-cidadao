<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use LoginCidadao\CoreBundle\Entity\City;
use LoginCidadao\CoreBundle\Entity\CityRepository;
use LoginCidadao\CoreBundle\Entity\Country;
use LoginCidadao\CoreBundle\Entity\CountryRepository;
use LoginCidadao\CoreBundle\Entity\IdCard;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Entity\PersonAddress;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Entity\StateRepository;
use LoginCidadao\CoreBundle\Model\LocationSelectData;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\DynamicFormBundle\Form\DynamicFormBuilder;
use LoginCidadao\DynamicFormBundle\Model\DynamicFormData;
use LoginCidadao\DynamicFormBundle\Service\DynamicFormService;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class DynamicFormServiceTest extends TestCase
{
    public function testGetDynamicFormDataWithStateId()
    {
        $target = $this->createMock('LoginCidadao\TaskStackBundle\Model\TaskTargetInterface');
        $task = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTask')
            ->disableOriginalConstructor()->getMock();
        $task->expects($this->once())->method('getTarget')->willReturn($target);

        $redirectUrl = 'https://example.com';
        $stackManager = $this->getTaskStackManager();
        $stackManager->expects($this->once())->method('getTargetUrl')->willReturn($redirectUrl);
        $stackManager->expects($this->once())->method('getNextTask')->willReturn($task);

        $state = new State();
        $stateRepo = $this->getLocationRepo('State');
        $stateRepo->expects($this->once())->method('find')->with(1)->willReturn($state);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')->willReturn($stateRepo);

        $formService = $this->getFormService($em, null, null, $stackManager, null);

        $person = $this->getPerson();
        /** @var MockObject|Request $request */
        $request = $this->getRequest();
        $request->expects($this->exactly(2))->method('get')->willReturnCallback(
            function ($key) {
                switch ($key) {
                    case 'id_card_state_id':
                        return 1;
                    case 'redirect_url':
                        return 'https://example.com';
                    default:
                        return null;
                }
            }
        );
        $scope = 'scope1 scope2';

        $data = $formService->getDynamicFormData($person, $request, $scope);
        $this->assertEquals($person, $data->getPerson());
        $this->assertEquals($scope, $data->getScope());
        $this->assertEquals($redirectUrl, $data->getRedirectUrl());
    }

    public function testGetDynamicFormDataWithoutState()
    {
        $target = $this->createMock('LoginCidadao\TaskStackBundle\Model\TaskTargetInterface');
        $task = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTask')
            ->disableOriginalConstructor()->getMock();
        $task->expects($this->once())->method('getTarget')->willReturn($target);

        $redirectUrl = 'https://example.com';
        $stackManager = $this->getTaskStackManager();
        $stackManager->expects($this->once())->method('getTargetUrl')->willReturn($redirectUrl);
        $stackManager->expects($this->once())->method('getNextTask')->willReturn($task);

        $formService = $this->getFormService(null, null, null, $stackManager, null);

        $person = $this->getPerson();
        $request = $this->getRequest();
        $scope = 'scope1 scope2';

        $data = $formService->getDynamicFormData($person, $request, $scope);
        $this->assertEquals($person, $data->getPerson());
        $this->assertEquals($scope, $data->getScope());
        $this->assertEquals($redirectUrl, $data->getRedirectUrl());
    }

    public function testGetDynamicFormDataWithoutRedirectUrlNorEvent()
    {
        $formService = $this->getFormService();

        $person = $this->getPerson();
        $request = $this->getRequest();
        $request->expects($this->atLeastOnce())->method('get')->willReturn(null);
        $scope = 'scope1 scope2';

        $data = $formService->getDynamicFormData($person, $request, $scope);
        $this->assertEquals($person, $data->getPerson());
        $this->assertEquals($scope, $data->getScope());
        $this->assertEquals('lc_dashboard', $data->getRedirectUrl());
    }

    public function testGetDynamicFormDataWithStateAcronym()
    {
        $target = $this->createMock('LoginCidadao\TaskStackBundle\Model\TaskTargetInterface');
        $task = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTask')
            ->disableOriginalConstructor()->getMock();
        $task->expects($this->once())->method('getTarget')->willReturn($target);

        $redirectUrl = 'https://example.com';
        $stackManager = $this->getTaskStackManager();
        $stackManager->expects($this->once())->method('getTargetUrl')->willReturn($redirectUrl);
        $stackManager->expects($this->once())->method('getNextTask')->willReturn($task);

        $state = new State();
        $stateRepo = $this->getLocationRepo('State');
        $stateRepo->expects($this->once())->method('findOneBy')->willReturn($state);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')->willReturn($stateRepo);

        $formService = $this->getFormService($em, null, null, $stackManager, null);

        $person = $this->getPerson();
        $request = $this->getRequest();
        $request->expects($this->atLeastOnce())->method('get')->willReturnCallback(
            function ($key) {
                switch ($key) {
                    case 'id_card_state':
                        return 'RS';
                    case 'redirect_url':
                        return 'https://example.com';
                    default:
                        return null;
                }
            }
        );
        $scope = 'scope1 scope2';

        $data = $formService->getDynamicFormData($person, $request, $scope);
        $this->assertEquals($person, $data->getPerson());
        $this->assertEquals($scope, $data->getScope());
        $this->assertEquals($redirectUrl, $data->getRedirectUrl());
    }

    public function testProcessInvalidForm()
    {
        $form = $this->getForm();
        $form->expects($this->once())->method('isValid')->willReturn(false);
        $request = $this->getRequest();

        $formService = $this->getFormService();
        $formService->processForm($form, $request);
    }

    public function testProcessFormWithPersonForm()
    {
        $data = new DynamicFormData();
        $data
            ->setPerson(new Person())
            ->setPlaceOfBirth(new LocationSelectData())
            ->setAddress(new PersonAddress())
            ->setIdCard(new IdCard())
            ->setRedirectUrl('https://example.com');

        $form = $this->getForm();
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($data);
        $form->expects($this->exactly(2))->method('has')->with('person')->willReturn(true);
        $form->expects($this->once())->method('get')->with('person')->willReturn($form);
        $request = $this->getRequest();

        $task = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTask')
            ->disableOriginalConstructor()->getMock();
        $stackManager = $this->getTaskStackManager();
        $stackManager->expects($this->once())->method('getCurrentTask')->willReturn($task);
        $stackManager->expects($this->once())->method('processRequest')->willReturnCallback(
            function ($request, $response) {
                return $response;
            }
        );

        $formService = $this->getFormService(null, null, null, $stackManager);
        $formService->processForm($form, $request);
    }

    public function testProcessFormWithoutPersonForm()
    {
        $data = new DynamicFormData();
        $data
            ->setPerson(new Person())
            ->setAddress(new PersonAddress())
            ->setRedirectUrl('https://example.com');

        $form = $this->getForm();
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($data);
        $form->expects($this->exactly(2))->method('has')->with('person')->willReturn(false);
        $form->expects($this->never())->method('get')->with('person')->willReturn(null);
        $request = $this->getRequest();

        $task = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTask')
            ->disableOriginalConstructor()->getMock();
        $stackManager = $this->getTaskStackManager();
        $stackManager->expects($this->once())->method('getCurrentTask')->willReturn($task);
        $stackManager->expects($this->once())->method('processRequest')->willReturnCallback(
            function ($request, $response) {
                return $response;
            }
        );

        $formService = $this->getFormService(null, null, null, $stackManager);
        $formService->processForm($form, $request);
    }

    public function testBuildForm()
    {
        $data = new DynamicFormData();
        $data->setPerson($this->getPerson());

        $form = $this->getForm();
        $form->expects($this->exactly(2))->method('add')->willReturn($form);

        $scopes = ['scope1', 'scope2'];

        $formService = $this->getFormService();
        $result = $formService->buildForm($form, $data, $scopes);

        $this->assertEquals($form, $result);
    }

    public function testGetClient()
    {
        $client = new Client();
        $client->setId(1)
            ->setRandomId('abc');

        $repo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\ClientRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('findOneBy')->with(
            ['id' => $client->getId(), 'randomId' => $client->getRandomId()]
        )->willReturn($client);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')->willReturn($repo);

        $clientId = $client->getPublicId();
        $formService = $this->getFormService($em);
        $result = $formService->getClient($clientId);

        $this->assertEquals($client, $result);
    }

    public function testGetClientBadId()
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid client_id.');

        $clientId = 'invalid';
        $formService = $this->getFormService();
        $formService->getClient($clientId);
    }

    public function testGetClientNotFound()
    {
        $this->expectException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $this->expectExceptionMessage('Client not found');

        $repo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\ClientRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('findOneBy')->willReturn(null);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')->willReturn($repo);

        $clientId = '1_abc';
        $formService = $this->getFormService($em);
        $formService->getClient($clientId);
    }

    public function testGetLocationDataFromRequestCity()
    {
        $country = new Country(1);
        $state = new State(1);
        $state->setCountry($country);
        $city = new City();
        $city->setId(1)
            ->setState($state);

        $repos = [
            'City' => $this->getLocationRepo('City'),
            'State' => $this->getLocationRepo('State'),
            'Country' => $this->getLocationRepo('Country'),
        ];
        $repos['City']->expects($this->once())->method('find')->with($city->getId())->willReturn($city);

        $em = $this->getEntityManager();
        $em->expects($this->exactly(3))->method('getRepository')->willReturnCallback(
            function ($entity) use (&$repos) {
                $parts = explode(':', $entity);

                return $repos[$parts[1]];
            }
        );

        $request = $this->getRequest();
        $request->expects($this->exactly(3))->method('get')->willReturn(1);

        $formService = $this->getFormService($em);
        $data = $formService->getLocationDataFromRequest($request);

        $this->assertEquals($country, $data->getPlaceOfBirth()->getCountry());
        $this->assertEquals($state, $data->getPlaceOfBirth()->getState());
        $this->assertEquals($city, $data->getPlaceOfBirth()->getCity());
    }

    public function testGetLocationDataFromRequestState()
    {
        $country = new Country(1);
        $state = new State(1);
        $state->setCountry($country);

        $repos = [
            'City' => $this->getLocationRepo('City'),
            'State' => $this->getLocationRepo('State'),
            'Country' => $this->getLocationRepo('Country'),
        ];
        $repos['State']->expects($this->once())->method('find')->with($state->getId())->willReturn($state);

        $em = $this->getEntityManager();
        $em->expects($this->exactly(3))->method('getRepository')->willReturnCallback(
            function ($entity) use (&$repos) {
                $parts = explode(':', $entity);

                return $repos[$parts[1]];
            }
        );

        $request = $this->getRequest();
        $request->expects($this->exactly(3))->method('get')->willReturn(1);

        $formService = $this->getFormService($em);
        $data = $formService->getLocationDataFromRequest($request);

        $this->assertEquals($country, $data->getPlaceOfBirth()->getCountry());
        $this->assertEquals($state, $data->getPlaceOfBirth()->getState());
        $this->assertNull($data->getPlaceOfBirth()->getCity());
    }

    public function testGetLocationDataFromRequestCountry()
    {
        $country = new Country(1);

        $repos = [
            'City' => $this->getLocationRepo('City'),
            'State' => $this->getLocationRepo('State'),
            'Country' => $this->getLocationRepo('Country'),
        ];
        $repos['Country']->expects($this->once())->method('find')->with($country->getId())->willReturn($country);

        $em = $this->getEntityManager();
        $em->expects($this->exactly(3))->method('getRepository')->willReturnCallback(
            function ($entity) use (&$repos) {
                $parts = explode(':', $entity);

                return $repos[$parts[1]];
            }
        );

        $request = $this->getRequest();
        $request->expects($this->exactly(3))->method('get')->willReturn(1);

        $formService = $this->getFormService($em);
        $data = $formService->getLocationDataFromRequest($request);

        $this->assertEquals($country, $data->getPlaceOfBirth()->getCountry());
        $this->assertNull($data->getPlaceOfBirth()->getState());
        $this->assertNull($data->getPlaceOfBirth()->getCity());
    }

    public function testGetLocationDataFromRequestNoIds()
    {
        $request = $this->getRequest();
        $request->expects($this->exactly(3))->method('get')->willReturn(null);

        $formService = $this->getFormService();
        $data = $formService->getLocationDataFromRequest($request);

        $this->assertNull($data->getPlaceOfBirth()->getCountry());
        $this->assertNull($data->getPlaceOfBirth()->getState());
        $this->assertNull($data->getPlaceOfBirth()->getCity());
    }

    public function testSkipCurrent()
    {
        $request = $this->getRequest();
        /** @var MockObject|Response $defaultResponse */
        $defaultResponse = $this->createMock('Symfony\Component\HttpFoundation\Response');

        $task = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTask')
            ->disableOriginalConstructor()->getMock();
        $stackManager = $this->getTaskStackManager();
        $stackManager->expects($this->once())->method('getCurrentTask')->willReturn($task);
        $stackManager->expects($this->once())->method('setTaskSkipped')->with($task);
        $stackManager->expects($this->once())->method('processRequest')->with($request, $defaultResponse)->willReturn(
            $defaultResponse
        );

        $formService = $this->getFormService(null, null, null, $stackManager);
        $response = $formService->skipCurrent($request, $defaultResponse);

        $this->assertEquals($defaultResponse, $response);
    }

    public function testGetSkipUrl()
    {
        $data = new DynamicFormData();
        $data->setRedirectUrl('https://example.com');

        $task = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTask')
            ->disableOriginalConstructor()->getMock();
        $stackManager = $this->getTaskStackManager();
        $stackManager->expects($this->once())->method('getCurrentTask')->willReturn($task);

        $formService = $this->getFormService(null, null, null, $stackManager);
        $url = $formService->getSkipUrl($data);

        $this->assertEquals('dynamic_form_skip', $url);
    }

    public function testGetSkipUrlNoTask()
    {
        $data = new DynamicFormData();
        $data->setRedirectUrl('https://example.com');

        $stackManager = $this->getTaskStackManager();
        $stackManager->expects($this->once())->method('getCurrentTask')->willReturn(null);

        $formService = $this->getFormService(null, null, null, $stackManager);
        $url = $formService->getSkipUrl($data);

        $this->assertEquals($data->getRedirectUrl(), $url);
    }

    /**
     * @param EntityManagerInterface|null $em
     * @param EventDispatcherInterface|null $dispatcher
     * @param UserManagerInterface|null $userManager
     * @param TaskStackManagerInterface|null $taskStackManager
     * @param DynamicFormBuilder|null $dynamicFormBuilder
     * @param RouterInterface|null $router
     * @return DynamicFormService
     */
    private function getFormService(
        $em = null,
        $dispatcher = null,
        $userManager = null,
        $taskStackManager = null,
        $dynamicFormBuilder = null,
        $router = null
    ): DynamicFormService {
        if (!$em) {
            $em = $this->getEntityManager();
        }
        if (!$dispatcher) {
            /** @var MockObject|EventDispatcherInterface $dispatcher */
            $dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        }
        if (!$userManager) {
            /** @var MockObject|UserManagerInterface $userManager */
            $userManager = $this->createMock('FOS\UserBundle\Model\UserManagerInterface');
        }
        if (!$taskStackManager) {
            $taskStackManager = $this->getTaskStackManager();
        }
        if (!$dynamicFormBuilder) {
            /** @var MockObject|DynamicFormBuilder $dynamicFormBuilder */
            $dynamicFormBuilder = $this->getMockBuilder('LoginCidadao\DynamicFormBundle\Form\DynamicFormBuilder')
                ->disableOriginalConstructor()
                ->getMock();
        }
        if (!$router) {
            /** @var MockObject|RouterInterface $router */
            $router = $this->createMock(RouterInterface::class);
            $router->expects($this->any())->method('generate')
                ->willReturnCallback(function ($name) {
                    return $name;
                });
        }

        $formService = new DynamicFormService(
            $em,
            $dispatcher,
            $userManager,
            $taskStackManager,
            $dynamicFormBuilder,
            $router
        );

        return $formService;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|TaskStackManagerInterface
     */
    private function getTaskStackManager()
    {
        return $this->createMock('LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface');
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|PersonInterface
     */
    private function getPerson()
    {
        return $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|EntityManagerInterface
     */
    private function getEntityManager()
    {
        return $this->createMock('Doctrine\ORM\EntityManagerInterface');
    }

    /**
     * @param $class
     * @return null|\PHPUnit\Framework\MockObject\MockObject|CountryRepository|StateRepository|CityRepository
     */
    private function getLocationRepo($class)
    {
        switch ($class) {
            case 'Country':
                return $this->getMockBuilder('LoginCidadao\CoreBundle\Entity\CountryRepository')
                    ->disableOriginalConstructor()->getMock();
            case 'State':
                return $this->getMockBuilder('LoginCidadao\CoreBundle\Entity\StateRepository')
                    ->disableOriginalConstructor()->getMock();
            case 'City':
                return $this->getMockBuilder('LoginCidadao\CoreBundle\Entity\CityRepository')
                    ->disableOriginalConstructor()->getMock();
            default:
                return null;
        }
    }

    /**
     * @return MockObject|Request
     */
    private function getRequest()
    {
        /** @var MockObject|Request $request */
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $request;
    }

    /**
     * @return MockObject|FormInterface
     */
    private function getForm()
    {
        return $this->createMock(FormInterface::class);
    }
}
