<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ClientFormType extends ClientBaseFormType
{
    protected $supportedScopes;
    protected $publicScopes;
    protected $reservedScopes;

    public function setScopes($publicScopes, $reservedScopes)
    {
        $this->reservedScopes = explode(' ', $reservedScopes);
        $this->publicScopes   = explode(' ', $publicScopes);


        $this->supportedScopes = $this->publicScopes + $this->reservedScopes;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $security = $this->security;
        $public   = array_combine($this->publicScopes, $this->publicScopes);
        $reserved = array_combine($this->reservedScopes, $this->reservedScopes);

        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($security, $public, $reserved) {
            $form = $event->getForm();

            if ($security->isGranted('ROLE_EDIT_CLIENT_ALLOWED_SCOPES')) {
                $form->add('allowedScopes', 'choice',
                    array(
                    'choices' => array(
                        'Public Scopes' => $public,
                        'Restricted Scopes' => $reserved,
                    ),
                    'multiple' => true,
                    'choices_as_values' => false,
                ));
            }
        });
    }

    public function getName()
    {
        return 'client_form_type';
    }
}
