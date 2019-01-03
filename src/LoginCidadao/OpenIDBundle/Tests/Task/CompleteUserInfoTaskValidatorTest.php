<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Task;

use FOS\OAuthServerBundle\Event\OAuthEvent;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTask;
use LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTaskValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class CompleteUserInfoTaskValidatorTest extends TestCase
{
    public function testGetCompleteUserInfoTaskCantSkipNoTask()
    {
        $user = new Person();
        $client = new Client();

        $request = $this->getRequest();
        $request->expects($this->exactly(3))
            ->method('get')->willReturnMap([
                ['_route', null, '_authorize_validate'],
                ['scope', false, 'scope1'],
                ['prompt', null, false],
                ['nonce', null, false],
            ]);

        $dispatcher = $this->getEventDispatcherInterface();
        $validator = new CompleteUserInfoTaskValidator($dispatcher, false);

        $validator->getCompleteUserInfoTask($user, $client, $request);
    }

    public function testGetCompleteUserInfoTaskCantSkip()
    {
        $user = new Person();
        $client = new Client();

        $request = $this->getRequest();
        $request->expects($this->exactly(4))
            ->method('get')->willReturnMap([
                ['_route', null, '_authorize_validate'],
                ['scope', false, 'name mobile country state city birthdate email cpf other'],
                ['prompt', null, false],
                ['nonce', null, false],
            ]);

        $dispatcher = $this->getEventDispatcherInterface();
        $validator = new CompleteUserInfoTaskValidator($dispatcher, false);

        $task = $validator->getCompleteUserInfoTask($user, $client, $request);

        $this->assertInstanceOf(CompleteUserInfoTask::class, $task);
        $this->assertSame('name mobile country state city birthdate email cpf', $task->getScope());
    }

    public function testGetCompleteUserInfoTaskCanSkip()
    {
        $user = new Person();
        $client = new Client();

        $request = $this->getRequest();
        $request->expects($this->exactly(3))
            ->method('get')->willReturnMap([
                ['_route', null, '_authorize_validate'],
                ['scope', false, 'scope1'],
                ['prompt', null, false],
                ['nonce', null, false],
            ]);

        $dispatcher = $this->getEventDispatcherInterface();
        $this->dispatcherExpectAuthorized($dispatcher);
        $validator = new CompleteUserInfoTaskValidator($dispatcher, true);

        $validator->getCompleteUserInfoTask($user, $client, $request);
    }

    public function testShouldPromptConsent()
    {
        $request = $this->getRequest();
        $params = $this->logicalOr($this->equalTo('prompt'), $this->equalTo('nonce'));
        $request->expects($this->exactly(2))
            ->method('get')->with($params)
            ->willReturnCallback(function ($key) {
                switch ($key) {
                    case 'prompt':
                        return 'consent';
                    case 'nonce':
                        return 'nonce';
                }

                return null;
            });

        $validator = new CompleteUserInfoTaskValidator($this->getEventDispatcherInterface(), true);

        $this->assertTrue($validator->shouldPromptConsent($request));
    }

    public function testShouldNotPromptConsentWithoutPrompt()
    {
        $request = $this->getRequest();
        $params = $this->logicalOr($this->equalTo('prompt'), $this->equalTo('nonce'));
        $request->expects($this->exactly(2))
            ->method('get')->with($params)
            ->willReturnCallback(function ($key) {
                return $key === 'prompt' ? 'consent' : null;
            });

        $validator = new CompleteUserInfoTaskValidator($this->getEventDispatcherInterface(), true);

        $this->assertFalse($validator->shouldPromptConsent($request));
    }

    public function testShouldNotPromptConsentWithoutNonce()
    {
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('get')->with('prompt')
            ->willReturn(null);

        $validator = new CompleteUserInfoTaskValidator($this->getEventDispatcherInterface(), true);

        $this->assertFalse($validator->shouldPromptConsent($request));
    }

    public function testIsClientAuthorized()
    {
        $dispatcher = $this->getEventDispatcherInterface();
        $this->dispatcherExpectAuthorized($dispatcher);
        $validator = new CompleteUserInfoTaskValidator($dispatcher, true);

        $this->assertTrue(
            $validator->isClientAuthorized(new Person(), new Client())
        );
    }

    public function testRouteInvalid()
    {
        $request = $this->getRequest();
        $request->expects($this->exactly(2))
            ->method('get')->willReturn(false);

        $validator = new CompleteUserInfoTaskValidator($this->getEventDispatcherInterface(), true);

        $this->assertFalse($validator->isRouteValid($request));
    }

    public function testRouteValid()
    {
        $request = $this->getRequest();
        $request->expects($this->exactly(2))
            ->method('get')->willReturnMap([
                ['_route', null, '_authorize_validate'],
                ['scope', false, 'scope1'],
            ]);

        $validator = new CompleteUserInfoTaskValidator($this->getEventDispatcherInterface(), true);

        $this->assertTrue($validator->isRouteValid($request));
    }

    /**
     * @return Request|MockObject
     */
    private function getRequest()
    {
        return $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return EventDispatcherInterface|MockObject
     */
    private function getEventDispatcherInterface()
    {
        return $this->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()->getMock();
    }

    private function dispatcherExpectAuthorized(MockObject $dispatcher)
    {
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(OAuthEvent::PRE_AUTHORIZATION_PROCESS, $this->isInstanceOf(OAuthEvent::class))
            ->willReturnCallback(function ($eventName, OAuthEvent $event) {
                $event->setAuthorizedClient(true);

                return $event;
            });
    }
}
