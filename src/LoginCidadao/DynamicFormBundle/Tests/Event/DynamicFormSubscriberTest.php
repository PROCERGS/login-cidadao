<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle\Tests\Event;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\DynamicFormBundle\Event\DynamicFormSubscriber;
use LoginCidadao\DynamicFormBundle\Model\DynamicFormData;
use LoginCidadao\DynamicFormBundle\Service\DynamicFormServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DynamicFormSubscriberTest extends TestCase
{
    public function testSubscribedEvents()
    {
        $expected = [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];

        $this->assertEquals($expected, DynamicFormSubscriber::getSubscribedEvents());
    }

    public function testFullPreSetData()
    {
        /** @var PersonInterface $person */
        $person = $this->createMock(PersonInterface::class);
        $form = $this->createMock('Symfony\Component\Form\FormInterface');

        $data = new DynamicFormData();
        $data
            ->setPerson($person)
            ->setScope('scope1 scope2');

        /** @var MockObject|FormEvent $event */
        $event = $this->getMockBuilder(FormEvent::class)
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getData')
            ->willReturn($data);
        $event->expects($this->once())->method('getForm')
            ->willReturn($form);

        $subscriber = $this->getDynamicFormSubscriber($this->getFormService(true));
        $subscriber->preSetData($event);
    }

    public function testInvalidData()
    {
        /** @var MockObject|FormEvent $event */
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getData')
            ->willReturn(null);

        $subscriber = $this->getDynamicFormSubscriber();
        $subscriber->preSetData($event);
    }

    public function testNoScope()
    {
        $data = new DynamicFormData();
        $data->setScope('');

        /** @var MockObject|FormEvent $event */
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getData')
            ->willReturn($data);

        $subscriber = $this->getDynamicFormSubscriber();
        $subscriber->preSetData($event);
    }

    private function getDynamicFormSubscriber(DynamicFormServiceInterface $formService = null)
    {
        $formService = $formService ?: $this->getFormService(false);
        $subscriber = new DynamicFormSubscriber($formService);

        return $subscriber;
    }

    /**
     * @param $expectsBuildForm
     * @return MockObject|DynamicFormServiceInterface
     */
    private function getFormService($expectsBuildForm)
    {
        $FormInterface = 'Symfony\Component\Form\FormInterface';
        $DynamicFormDataClass = 'LoginCidadao\DynamicFormBundle\Model\DynamicFormData';

        $formService = $this->createMock(DynamicFormServiceInterface::class);

        if ($expectsBuildForm) {
            $formService->expects($this->once())->method('buildForm')
                ->with(
                    $this->isInstanceOf($FormInterface),
                    $this->isInstanceOf($DynamicFormDataClass),
                    $this->isType('array')
                );
        }

        return $formService;
    }
}
