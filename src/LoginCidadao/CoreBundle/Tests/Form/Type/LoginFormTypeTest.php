<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\AccessSession;
use LoginCidadao\CoreBundle\Form\Type\LoginFormType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class LoginFormTypeTest extends TestCase
{
    public function testGetBlockPrefix()
    {
        $form = new LoginFormType();
        $this->assertSame('login_form_type', $form->getBlockPrefix());
    }

    /**
     * @throws \Exception
     */
    public function testBuildFormWithoutCaptcha()
    {
        $ip = '::1';
        $username = 'some.username';
        $accessSession = new AccessSession();

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())
            ->method('findOneBy')->with(['ip' => $ip, 'username' => $username])
            ->willReturn($accessSession);

        /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->once())
            ->method('getRepository')->with('LoginCidadaoCoreBundle:AccessSession')
            ->willReturn($repo);

        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->expects($this->once())
            ->method('get')->with(Security::LAST_USERNAME)->willReturn($username);

        $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
        $request->expects($this->once())->method('getSession')->willReturn($session);
        $request->expects($this->once())->method('getClientIp')->willReturn($ip);

        /** @var FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject $builder */
        $builder = $this->createMock('Symfony\Component\Form\FormBuilderInterface');
        $builder->expects($this->exactly(2))->method('add')->with($this->logicalOr(
            $this->equalTo('username'),
            $this->equalTo('password')
        ));

        $form = new LoginFormType();
        $form->setContainer($this->getContainer($em, $request, 2));
        $form->buildForm($builder, []);
    }

    /**
     * @throws \Exception
     */
    public function testBuildFormWithCaptcha()
    {
        $ip = '::1';
        $username = 'some.username';
        $accessSession = new AccessSession();
        $accessSession->setVal(5);

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())
            ->method('findOneBy')->with(['ip' => $ip, 'username' => $username])
            ->willReturn($accessSession);

        /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->once())
            ->method('getRepository')->with('LoginCidadaoCoreBundle:AccessSession')
            ->willReturn($repo);

        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->expects($this->once())
            ->method('get')->with(Security::LAST_USERNAME)->willReturn($username);

        $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
        $request->expects($this->once())->method('getSession')->willReturn($session);
        $request->expects($this->once())->method('getClientIp')->willReturn($ip);

        /** @var FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject $builder */
        $builder = $this->createMock('Symfony\Component\Form\FormBuilderInterface');
        $builder->expects($this->exactly(3))->method('add')->with($this->logicalOr(
            $this->equalTo('username'),
            $this->equalTo('password'),
            $this->equalTo('recaptcha')
        ));

        $form = new LoginFormType();
        $form->setContainer($this->getContainer($em, $request, 2));
        $form->buildForm($builder, []);
    }

    /**
     * @throws \Exception
     */
    public function testSetContainer()
    {
        (new LoginFormType())->setContainer($this->getContainer());
    }

    public function testConfigureOptions()
    {
        /** @var OptionsResolver|\PHPUnit_Framework_MockObject_MockObject $resolver */
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()->getMock();
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with([
                'csrf_protection' => true,
                'csrf_field_name' => 'csrf_token',
                'csrf_token_id' => 'authenticate',
                'check_captcha' => null,
            ]);
        $form = new LoginFormType();
        $form->configureOptions($resolver);
    }

    /**
     * @param EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject|null $em
     * @param Request|\PHPUnit_Framework_MockObject_MockObject|null $request
     * @param int|null $threshold
     * @return ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getContainer($em = null, $request = null, $threshold = null)
    {
        $requestStack = new RequestStack();
        if (null !== $request) {
            $requestStack->push($request);
        }

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['doctrine.orm.entity_manager', 1, $em],
                ['request_stack', 1, $requestStack],
            ]);
        $container->expects($this->once())
            ->method('getParameter')
            ->with('brute_force_threshold')
            ->willReturn($threshold);

        return $container;
    }
}
