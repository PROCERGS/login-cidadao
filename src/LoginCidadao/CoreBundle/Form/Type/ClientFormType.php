<?php

namespace LoginCidadao\CoreBundle\Form\Type;

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
            function(FormEvent $event) use ($security, $public, $reserved) {
            $form = $event->getForm();

            if ($security->isGranted('ROLE_EDIT_CLIENT_ALLOWED_SCOPES') === false) {
                return;
            }

            $choices = array(
                'Public Scopes' => array_flip($public)
            );
            if ($security->isGranted('ROLE_EDIT_CLIENT_ALLOWED_RESTRICTED_SCOPES')) {
                $choices['Restricted Scopes'] = array_flip($reserved);
            }

            $form->add('allowedScopes',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                array(
                'choices' => $choices,
                'multiple' => true,
                'choices_as_values' => true,
            ));
        });
    }
}
