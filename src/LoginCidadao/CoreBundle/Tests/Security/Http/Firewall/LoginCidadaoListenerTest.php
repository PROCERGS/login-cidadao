<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Security\Http\Firewall;

use LoginCidadao\CoreBundle\Security\Http\Firewall\LoginCidadaoListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class LoginCidadaoListenerTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testLoginListenerBadCredentials()
    {
        $this->expectException('Symfony\Component\Security\Core\Exception\BadCredentialsException');
        $username = 'myUser';
        $ip = '::1';
        $threshold = 2;


        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()->getMock();
        $request->expects($this->once())->method('getSession')->willReturn($session);
        $request->expects($this->exactly(2))->method('getClientIp')->willReturn($ip);
        $request->expects($this->exactly(2))->method('get')->willReturn($username);

        $listener = $this->getListener($threshold, $username, $ip, $this->getForm());
        $this->invokeMethod($listener, 'attemptAuthentication', [$request]);
    }

    /**
     * @throws \ReflectionException
     */
    public function testLoginListenerBadCaptcha()
    {
        $this->expectException('LoginCidadao\CoreBundle\Exception\RecaptchaException');

        $username = 'myUser';
        $ip = '::1';
        $threshold = 2;

        /** @var MockObject|FormInterface $generic */
        $generic = $this->createMock(FormInterface::class);
        $generic->expects($this->once())
            ->method('getName')->willReturn('generic');

        /** @var MockObject|FormInterface $recaptcha */
        $recaptcha = $this->createMock(FormInterface::class);
        $recaptcha->expects($this->once())
            ->method('getName')->willReturn('recaptcha');

        $genericError = new FormError('generic');
        $genericError->setOrigin($generic);
        $captchaError = new FormError('captcha');
        $captchaError->setOrigin($recaptcha);
        $errors = [$genericError, $captchaError];

        $form = $this->getForm();
        $form->expects($this->once())->method('getErrors')->willReturn($errors);

        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()->getMock();
        $request->expects($this->once())->method('getSession')->willReturn($session);
        $request->expects($this->exactly(2))->method('getClientIp')->willReturn($ip);
        $request->expects($this->exactly(2))->method('get')->willReturn($username);

        $listener = $this->getListener($threshold, $username, $ip, $form);
        $this->invokeMethod($listener, 'attemptAuthentication', [$request]);
    }

    /**
     * @throws \ReflectionException
     */
    public function testCallsParent()
    {
        $username = 'myUser';
        $ip = '::1';
        $threshold = 2;

        $form = $this->getForm(true);

        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()->getMock();
        $request->expects($this->exactly(2))->method('getSession')->willReturn($session);
        $request->expects($this->exactly(2))->method('getClientIp')->willReturn($ip);
        if ($request instanceof Request && $request->request === null) {
            $request->request = new ParameterBag(['username' => $username]);
        }

        $listener = $this->getListener($threshold, $username, $ip, $form, true);
        $this->invokeMethod($listener, 'attemptAuthentication', [$request]);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @throws \ReflectionException
     */
    private function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    private function getListener($threshold, $username, $ip, $form, $postOnly = false)
    {
        $tokenStorage = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $authManager = $this->createMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $sessionStrategy = $this->createMock('Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface');
        $httpUtils = $this->createMock('Symfony\Component\Security\Http\HttpUtils');
        $providerKey = 'provider';
        $authSuccessHandler = $this->createMock('Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface');
        $authFailHandler = $this->createMock('Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface');
        $options = [
            'post_only' => $postOnly,
            'username_parameter' => 'username',
        ];
        $logger = $this->createMock('Psr\Log\LoggerInterface');
        $dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $csrfTokenManager = null;

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())
            ->method('findOneBy')->with(['username' => $username, 'ip' => $ip]);

        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->once())->method('getRepository')->with('LoginCidadaoCoreBundle:AccessSession')
            ->willReturn($repo);

        /** @var FormFactoryInterface|\PHPUnit_Framework_MockObject_MockObject $formFactory */
        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('create')
            ->willReturn($form);

        /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject $translator */
        $translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');

        $listener = new LoginCidadaoListener(
            $tokenStorage,
            $authManager,
            $sessionStrategy,
            $httpUtils,
            $providerKey,
            $authSuccessHandler,
            $authFailHandler,
            $options,
            $logger,
            $dispatcher,
            $csrfTokenManager,
            $em
        );
        $listener->setBruteForceThreshold($threshold);
        $listener->setFormFactory($formFactory);
        $listener->setTranslator($translator);

        return $listener;
    }

    private function getForm($isValid = false)
    {
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn($isValid);

        return $form;
    }
}
