<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle\Event;

use LoginCidadao\DynamicFormBundle\Model\DynamicFormData;
use LoginCidadao\DynamicFormBundle\Service\DynamicFormServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class DynamicFormSubscriber implements EventSubscriberInterface
{
    /** @var  DynamicFormServiceInterface */
    private $formService;

    /**
     * DynamicFormSubscriber constructor.
     * @param DynamicFormServiceInterface $formService
     */
    public function __construct(DynamicFormServiceInterface $formService)
    {
        $this->formService = $formService;
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event)
    {
        /** @var DynamicFormData $data */
        $data = $event->getData();

        if (!$data instanceof DynamicFormData) {
            return;
        }

        if (trim($data->getScope()) === '') {
            return;
        }
        $scopes = explode(' ', trim($data->getScope()));

        /** @var FormInterface $form */
        $form = $event->getForm();

        $this->formService->buildForm($form, $data, $scopes);
    }
}
