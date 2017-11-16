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

use LoginCidadao\DynamicFormBundle\Event\DynamicFormSubscriber;
use LoginCidadao\DynamicFormBundle\Model\DynamicFormData;
use LoginCidadao\DynamicFormBundle\Service\DynamicFormServiceInterface;
use Symfony\Component\Form\FormEvents;

class DynamicFormSubscriberTest extends \PHPUnit_Framework_TestCase
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
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $form = $this->getMock('Symfony\Component\Form\FormInterface');

        $data = new DynamicFormData();
        $data
            ->setPerson($person)
            ->setScope('scope1 scope2');

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
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
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getData')
            ->willReturn(null);

        $subscriber = $this->getDynamicFormSubscriber();
        $subscriber->preSetData($event);
    }

    public function testNoScope()
    {
        $form = $this->getMock('Symfony\Component\Form\FormInterface');

        $data = new DynamicFormData();
        $data->setScope('');

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

    private function getFormService($expectsBuildForm)
    {
        $FormInterface = 'Symfony\Component\Form\FormInterface';
        $DynamicFormDataClass = 'LoginCidadao\DynamicFormBundle\Model\DynamicFormData';

        $formService = $this->getMock('LoginCidadao\DynamicFormBundle\Service\DynamicFormServiceInterface');

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
