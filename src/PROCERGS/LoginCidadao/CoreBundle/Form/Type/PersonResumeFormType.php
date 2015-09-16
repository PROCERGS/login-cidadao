<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\CommonFormType;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PROCERGS\LoginCidadao\CoreBundle\Helper\SecurityHelper;

class PersonResumeFormType extends CommonFormType
{
    /** @var SecurityHelper */
    private $securityHelper;

    public function __construct(SecurityHelper $securityHelper)
    {
        $this->securityHelper = $securityHelper;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'available_roles' => array()
        ));
    }

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

        $user = $this->getUser();

        $allRoles       = $this->translateRoles($builder->getOption('available_roles'));
        $securityHelper = $this->securityHelper;
        $security       = $this->security;
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($user, &$allRoles, &$securityHelper, &$security) {
            $person = $event->getData();
            $form   = PersonResumeFormType::populateCountryStateCity($person,
                    $event->getForm());

            $roles = PersonResumeFormType::filterRoles($person, $user, $form,
                    $allRoles, $securityHelper, $security);
        });
    }

    public function getName()
    {
        return 'person_resume_form_type';
    }

    public static function populateCountryStateCity(PersonInterface $person,
                                                    FormInterface $form)
    {
        $country = $person->getCountry();
        $state   = $person->getState();
        $city    = $person->getCity();

        $countryName = '';
        if ($country) {
            $countryName = $country->getName();
        }
        $form->add('country', 'text',
            array(
            'required' => true,
            'mapped' => false,
            'read_only' => true,
            'data' => $countryName,
        ));

        $stateName = '';
        if ($state) {
            $stateName = $state->getName();
        }
        $form->add('state', 'text',
            array(
            'required' => true,
            'read_only' => 'true',
            'mapped' => false,
            'read_only' => true,
            'data' => $stateName,
        ));

        $cityName = '';
        if ($city) {
            $cityName = $city->getName();
        }
        $form->add('city', 'text',
            array(
            'required' => true,
            'read_only' => 'true',
            'mapped' => false,
            'read_only' => true,
            'data' => $cityName,
        ));

        return $form;
    }

    private function translateRoles($roles)
    {
        $translated = array();
        foreach ($roles as $role) {
            if ($role == 'ROLE_ALLOWED_TO_SWITCH') {
                continue;
            }
            $translated[$role] = $this->translator->trans($role);
        }
        return $translated;
    }

    public static function filterRoles(PersonInterface $person,
                                       PersonInterface $loggedUser,
                                       FormInterface $form, array $roles,
                                       $securityHelper, $security)
    {
        $loggedUserLevel        = $securityHelper->getLoggedInUserLevel();
        $targetPersonLevel      = $securityHelper->getTargetPersonLevel($person);
        $isLoggedUserSuperAdmin = $security->isGranted('ROLE_SUPER_ADMIN');

        $filteredRoles = array();
        foreach ($roles as $role => $name) {
            $isFeature = preg_match('/^FEATURE_/', $role) === 1;
            if (!$isLoggedUserSuperAdmin && $isFeature) {
                continue;
            }

            if ($loggedUserLevel < $securityHelper->getRoleLevel($role)) {
                continue;
            }

            $filteredRoles[$role] = $name;
        }

        $form->add('roles', 'choice',
            array(
            'choices' => $filteredRoles,
            'multiple' => true,
            'read_only' => ($targetPersonLevel > $loggedUserLevel),
            'disabled' => ($targetPersonLevel > $loggedUserLevel)
        ));
        return $filteredRoles;
    }
}
