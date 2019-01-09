<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Security\User\Provider;

use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Security\Exception\AlreadyLinkedAccount;
use LoginCidadao\CoreBundle\Security\User\Provider\FOSUBUserProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FOSUBUserProviderTest extends TestCase
{
    /**
     * @throws AlreadyLinkedAccount
     */
    public function testConnect()
    {
        $serviceName = 'facebook';
        $setter = 'setFacebook';
        $username = 'username';
        $accessToken = 'accessToken';
        $facebookData = [
            'id' => 'fbId',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'birthday' => '01/01/1980',
            'username' => 'fbUsername',
        ];

        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $resourceOwner->expects($this->atLeastOnce())->method('getName')->willReturn($serviceName);

        /** @var MockObject|PersonInterface $user */
        $user = $this->createMock(PersonInterface::class);
        $user->expects($this->atLeastOnce())->method("{$setter}Id")->willReturn($user);
        $user->expects($this->once())->method("{$setter}AccessToken")->willReturn($user);
        $user->expects($this->atLeastOnce())->method("{$setter}Username")->willReturn($user);

        $user->expects($this->once())->method('setFirstName')->with($facebookData['first_name']);
        $user->expects($this->once())->method('setSurname')->with($facebookData['last_name']);
        $user->expects($this->once())->method('setEmail')->with($facebookData['email']);
        $user->expects($this->once())->method('setBirthdate')->with($this->isInstanceOf(\DateTime::class));

        $userManager = $this->getUserManager();
        $userManager->expects($this->once())->method('updateUser')->with($user);
        $userManager->expects($this->once())->method('findUserBy')
            ->with($this->isType('array'))
            ->willReturn(null);

        /** @var MockObject|UserResponseInterface $response */
        $response = $this->createMock(UserResponseInterface::class);
        $response->expects($this->atLeastOnce())->method('getResourceOwner')->willReturn($resourceOwner);
        $response->expects($this->atLeastOnce())->method('getUsername')->willReturn($username);
        $response->expects($this->atLeastOnce())->method('getAccessToken')->willReturn($accessToken);
        $response->expects($this->atLeastOnce())->method('getData')->willReturn($facebookData);

        $provider = new FOSUBUserProvider(
            $userManager, $this->getSession(), $this->getEventDispatcher(),
            $this->getFormFactory(), $this->getValidator(), $this->getRequestStack(), []);

        $provider->connect($user, $response);
    }

    public function testConnectAlreadyLinkedAccount()
    {
        $this->expectException(AlreadyLinkedAccount::class);

        $serviceName = 'facebook';
        $username = 'username';

        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $resourceOwner->expects($this->atLeastOnce())->method('getName')->willReturn($serviceName);

        /** @var MockObject|PersonInterface $user */
        $user = $this->createMock(PersonInterface::class);
        $user->expects($this->once())->method('getId')->willReturn(321);

        /** @var MockObject|PersonInterface $anotherUser */
        $anotherUser = $this->createMock(PersonInterface::class);
        $anotherUser->expects($this->once())->method('getId')->willReturn(123);

        $userManager = $this->getUserManager();
        $userManager->expects($this->once())->method('findUserBy')
            ->with($this->isType('array'))
            ->willReturn($anotherUser);

        /** @var MockObject|UserResponseInterface $response */
        $response = $this->createMock(UserResponseInterface::class);
        $response->expects($this->atLeastOnce())->method('getResourceOwner')->willReturn($resourceOwner);
        $response->expects($this->atLeastOnce())->method('getUsername')->willReturn($username);

        $provider = new FOSUBUserProvider(
            $userManager, $this->getSession(), $this->getEventDispatcher(),
            $this->getFormFactory(), $this->getValidator(), $this->getRequestStack(), []);

        $provider->connect($user, $response);
    }

    public function testOAuthLogin()
    {
        $serviceName = 'facebook';
        $setter = 'setFacebook';

        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $resourceOwner->expects($this->atLeastOnce())->method('getName')->willReturn($serviceName);

        $response = $this->createMock(UserResponseInterface::class);
        $response->expects($this->once())->method('getResourceOwner')->willReturn($resourceOwner);
        $response->expects($this->once())->method('getRealName')->willReturn('John Doe');
        $response->expects($this->once())->method('getUsername')->willReturn('john.doe');
        $response->expects($this->once())->method('getEmail')->willReturn('john.doe@example.com');
        $response->expects($this->once())->method('getNickname')->willReturn('john.doe');
        $response->expects($this->exactly(2))->method('getAccessToken')->willReturn('the_access_token');

        $user = $this->createMock(PersonInterface::class);
        $user->expects($this->once())->method("{$setter}AccessToken")->willReturn($user);

        $userManager = $this->getUserManager();
        $userManager->expects($this->once())->method('findUserBy')->willReturn($user);

        $provider = new FOSUBUserProvider(
            $userManager, $this->getSession(), $this->getEventDispatcher(),
            $this->getFormFactory(), $this->getValidator(), $this->getRequestStack(), []);

        $resultingUser = $provider->loadUserByOAuthUserResponse($response);

        $this->assertSame($user, $resultingUser);
    }

    public function testOAuthNewUser()
    {
        $serviceName = 'facebook';
        $setter = 'setFacebook';
        $facebookData = [
            'id' => 'fbId',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'birthday' => '01/01/1980',
            'username' => 'fbUsername',
        ];

        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $resourceOwner->expects($this->atLeastOnce())->method('getName')->willReturn($serviceName);

        $response = $this->createMock(UserResponseInterface::class);
        $response->expects($this->atLeastOnce())->method('getResourceOwner')->willReturn($resourceOwner);
        $response->expects($this->atLeastOnce())->method('getData')->willReturn($facebookData);
        $response->expects($this->once())->method('getRealName')->willReturn('John Doe');
        $response->expects($this->atLeastOnce())->method('getUsername')->willReturn('john.doe');
        $response->expects($this->once())->method('getEmail')->willReturn('john.doe@example.com');
        $response->expects($this->once())->method('getNickname')->willReturn('john.doe');
        $response->expects($this->once())->method('getAccessToken')->willReturn('the_access_token');

        $user = $this->createMock(PersonInterface::class);
        $user->expects($this->atLeastOnce())->method("{$setter}Id")->willReturn($user);
        $user->expects($this->once())->method("{$setter}AccessToken")->willReturn($user);
        $user->expects($this->atLeastOnce())->method("{$setter}Username")->willReturn($user);

        $user->expects($this->atLeastOnce())->method('setFirstName')->with($facebookData['first_name']);
        $user->expects($this->atLeastOnce())->method('setSurname')->with($facebookData['last_name']);
        $user->expects($this->atLeastOnce())->method('setEmail')->with($facebookData['email']);
        $user->expects($this->once())->method('setBirthdate')->with($this->isInstanceOf(\DateTime::class));

        $userManager = $this->getUserManager();
        $userManager->expects($this->once())->method('findUserBy')->willReturn(null);
        $userManager->expects($this->once())->method('createUser')->willReturn($user);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('setData')->with($user);

        $formFactory = $this->getFormFactory();
        $formFactory->expects($this->once())->method('createForm')->willReturn($form);

        $requestStack = $this->getRequestStack();
        $requestStack->expects($this->once())->method('getCurrentRequest')->willReturn(new Request());

        $provider = new FOSUBUserProvider(
            $userManager, $this->getSession(), $this->getEventDispatcher(),
            $formFactory, $this->getValidator(), $requestStack, []);

        $resultingUser = $provider->loadUserByOAuthUserResponse($response);

        $this->assertSame($user, $resultingUser);
    }

    /**
     * @return MockObject|UserManagerInterface
     */
    private function getUserManager()
    {
        return $this->createMock(UserManagerInterface::class);
    }

    /**
     * @return MockObject|SessionInterface
     */
    private function getSession()
    {
        return $this->createMock(SessionInterface::class);
    }

    /**
     * @return MockObject|EventDispatcherInterface
     */
    private function getEventDispatcher()
    {
        return $this->createMock(EventDispatcher::class);
    }

    /**
     * @return MockObject|FactoryInterface
     */
    private function getFormFactory()
    {
        return $this->createMock(FactoryInterface::class);
    }

    /**
     * @return MockObject|ValidatorInterface
     */
    private function getValidator()
    {
        return $this->createMock(ValidatorInterface::class);
    }

    /**
     * @return MockObject|RequestStack
     */
    private function getRequestStack()
    {
        return $this->createMock(RequestStack::class);
    }

    private function getProvider()
    {
        return new FOSUBUserProvider(
            $this->getUserManager(), $this->getSession(), $this->getEventDispatcher(),
            $this->getFormFactory(), $this->getValidator(), $this->getRequestStack(), []);
    }
}
