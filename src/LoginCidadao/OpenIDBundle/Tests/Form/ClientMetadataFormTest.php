<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Form;

use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Form\ClientMetadataForm;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientMetadataFormTest extends TestCase
{

    public function testGetName()
    {
        $form = new ClientMetadataForm($this->getClientManager());
        $this->assertSame('', $form->getName());
    }

    public function testConfigureOptions()
    {
        /** @var OptionsResolver|\PHPUnit_Framework_MockObject_MockObject $resolver */
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')->getMock();
        $resolver->expects($this->once())
            ->method('setDefaults')->with([
                'data_class' => 'LoginCidadao\OpenIDBundle\Entity\ClientMetadata',
                'csrf_protection' => false,
            ]);

        $form = new ClientMetadataForm($this->getClientManager());
        $form->configureOptions($resolver);
    }

    public function testBuildForm()
    {
        /** @var FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject $builder */
        $builder = $this->createMock('Symfony\Component\Form\FormBuilderInterface');
        $builder->expects($this->exactly(33))->method('add')->willReturn($builder);

        $form = new ClientMetadataForm($this->getClientManager());

        $builder->expects($this->once())
            ->method('addEventListener')->with(FormEvents::SUBMIT, [$form, 'onSubmit'])
            ->willReturn($builder);
        $form->buildForm($builder, []);
    }

    public function testOnSubmit()
    {
        $metadata = new ClientMetadata();

        $clientManager = $this->getClientManager();
        $clientManager->expects($this->once())
            ->method('populateNewMetadata')->with($metadata);

        /** @var FormInterface $formInterface */
        $formInterface = $this->createMock('Symfony\Component\Form\FormInterface');
        $event = new FormEvent($formInterface, $metadata);

        $form = new ClientMetadataForm($clientManager);
        $form->onSubmit($event);
    }

    /**
     * @return ClientManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClientManager()
    {
        return $this->getMockBuilder('LoginCidadao\OpenIDBundle\Manager\ClientManager')
            ->disableOriginalConstructor()->getMock();
    }
}
