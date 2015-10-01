<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ClientFormType extends ClientBaseFormType
{
    protected $lcScope;

    public function setLcScope($var)
    {
        $this->lcScope = $var;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $security = $this->security;

        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($security) {
            $form = $event->getForm();

            if ($security->isGranted('ROLE_EDIT_CLIENT_ALLOWED_SCOPES')) {
                $form->add('allowedScopes', 'collection');
            }
        });
    }

    public function getName()
    {
        return 'client_form_type';
    }
}
