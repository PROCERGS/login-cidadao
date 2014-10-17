<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\CommonFormType;

class PersonResumeFormType extends CommonFormType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', null,
                      array(
            'read_only' => 'true',
        ));
        $builder->add('email', 'email',
                      array(
            'label' => 'form.email',
            'read_only' => 'true',
            'translation_domain' => 'FOSUserBundle'
        ));
        $builder->add('firstName', 'text',
                      array(
            'label' => 'form.firstName',
            'read_only' => 'true',
            'translation_domain' => 'FOSUserBundle'
        ));
        $builder->add('surname', 'text',
                      array(
            'label' => 'form.surname',
            'read_only' => 'true',
            'translation_domain' => 'FOSUserBundle'
        ));
        $builder->add('birthdate', 'birthday',
                      array(
            'required' => false,
            'read_only' => 'true',
            'format' => 'yyyy-MM-dd',
            'widget' => 'single_text',
            'label' => 'form.birthdate',
            'translation_domain' => 'FOSUserBundle'
        ));
        $builder->add('mobile', null,
                      array(
            'required' => false,
            'read_only' => 'true',
            'label' => 'form.mobile',
            'translation_domain' => 'FOSUserBundle'
        ));
        $builder->add('image');
        $em = $this->em;
        $user = $this->getUser();
        $allRoles = array(
            'ROLE_FACEBOOK' => $this->translator->trans('ROLE_FACEBOOK'),
            'ROLE_DEV' => $this->translator->trans('ROLE_DEV'),
            'ROLE_SUPER' => $this->translator->trans('ROLE_SUPER')
        );
        $allRoles['ROLE_ADMIN'] = $this->translator->trans('ROLE_ADMIN');

        $builder->addEventListener(FormEvents::PRE_SET_DATA,
                                   function (FormEvent $event) use (&$em, &$user, &$allRoles) {
            $person = $event->getData();
            $form = $event->getForm();
            $name = '';
            if ($var = $person->getCountry()) {
                $name = $var->getName();
            }
            $form->add('country', 'text',
                       array(
                'required' => true,
                'mapped' => false,
                'read_only' => true,
                'data' => $name,
            ));
            $name = '';
            if ($var = $person->getState()) {
                $name = $var->getName();
            }
            $form->add('state', 'text',
                       array(
                'required' => true,
                'read_only' => 'true',
                'mapped' => false,
                'read_only' => true,
                'data' => $name,
            ));
            $name = '';
            if ($var = $person->getCity()) {
                $name = $var->getName();
            }
            $form->add('city', 'text',
                       array(
                'required' => true,
                'read_only' => 'true',
                'mapped' => false,
                'read_only' => true,
                'data' => $name,
            ));
            $isUserAdmin = in_array('ROLE_ADMIN', $user->getRoles());
            $isPersonAdmin = in_array('ROLE_ADMIN', $person->getRoles());
            if ($isPersonAdmin) {
                if ($isUserAdmin) {
                    $form->add('roles', 'choice',
                               array(
                        'choices' => $allRoles,
                        'multiple' => true,
                    ));
                }
            } else {
                if (!$isUserAdmin) {
                    unset($allRoles['ROLE_ADMIN']);
                }
                $form->add('roles', 'choice',
                           array(
                    'choices' => $allRoles,
                    'multiple' => true,
                ));
            }
        });
    }

    public function getName()
    {
        return 'person_resume_form_type';
    }

}
