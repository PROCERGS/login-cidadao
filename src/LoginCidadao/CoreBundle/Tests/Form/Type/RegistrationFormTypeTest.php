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

use LoginCidadao\CoreBundle\Form\Type\RegistrationFormType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RegistrationFormTypeTest extends TestCase
{

    public function testBuildForm()
    {
        $session = $this->getSession();
        $session->expects($this->once())->method('has')->with('requested_scope')->willReturn(true);
        $session->expects($this->once())->method('get')->with('requested_scope')
            ->willReturn('name cpf mobile birthdate');

        $builder = $this->getFormBuilder();
        $builder->expects($this->exactly(7))
            ->method('add')->with($this->logicalOr(
                $this->equalTo('email'),
                $this->equalTo('plainPassword'),
                $this->equalTo('firstName'),
                $this->equalTo('surname'),
                $this->equalTo('cpf'),
                $this->equalTo('mobile'),
                $this->equalTo('birthdate')
            ))
            ->willReturn($builder);

        $form = new RegistrationFormType('LoginCidadao\CoreBundle\Model\PersonInterface', $session);
        $form->buildForm($builder, []);
    }

    public function testGetBlockPrefix()
    {
        $form = new RegistrationFormType('', $this->getSession());
        $this->assertSame('lc_person_registration', $form->getBlockPrefix());
    }

    /**
     * @return SessionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSession()
    {
        return $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
    }

    /**
     * @return FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getFormBuilder()
    {
        return $this->createMock('Symfony\Component\Form\FormBuilderInterface');
    }
}
