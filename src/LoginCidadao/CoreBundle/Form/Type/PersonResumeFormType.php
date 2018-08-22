<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use LoginCidadao\CoreBundle\Helper\SecurityHelper;

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
        $resolver->setDefaults([
            'available_roles' => [],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', null, ['attr' => ['readonly' => true]]);
        $builder->add('email', EmailType::class, [
            'label' => 'form.email',
            'attr' => ['readonly' => true],
            'translation_domain' => 'FOSUserBundle',
        ]);
        $builder->add('firstName', TextType::class, [
            'label' => 'form.firstName',
            'attr' => ['readonly' => true],
            'translation_domain' => 'FOSUserBundle',
        ]);
        $builder->add('surname', TextType::class, [
            'label' => 'form.surname',
            'attr' => ['readonly' => true],
            'translation_domain' => 'FOSUserBundle',
        ]);
        $builder->add('birthdate', BirthdayType::class, [
            'required' => false,
            'attr' => ['readonly' => true],
            'format' => 'yyyy-MM-dd',
            'widget' => 'single_text',
            'label' => 'form.birthdate',
            'translation_domain' => 'FOSUserBundle',
        ]);
        $builder->add('mobile', PhoneNumberType::class, [
            'required' => false,
            'attr' => ['readonly' => true],
            'label' => 'form.mobile',
            'translation_domain' => 'FOSUserBundle',
        ]);

        $user = $this->getUser();

        $allRoles = $this->translateRoles($builder->getOption('available_roles'));
        $securityHelper = $this->securityHelper;
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($user, $allRoles, $securityHelper) {
                $person = $event->getData();
                $form = PersonResumeFormType::populateCountryStateCity(
                    $person,
                    $event->getForm()
                );

                $roles = PersonResumeFormType::filterRoles($person, $form, $allRoles, $securityHelper);
            }
        );
    }

    public function getName()
    {
        return 'person_resume_form_type';
    }

    public static function populateCountryStateCity(
        PersonInterface $person,
        FormInterface $form
    ) {
        $country = $person->getCountry();
        $state = $person->getState();
        $city = $person->getCity();

        $countryName = '';
        if ($country) {
            $countryName = $country->getName();
        }
        $form->add('country', TextType::class, [
            'required' => true,
            'mapped' => false,
            'attr' => ['readonly' => true],
            'data' => $countryName,
        ]);

        $stateName = '';
        if ($state) {
            $stateName = $state->getName();
        }
        $form->add('state', TextType::class, [
            'required' => true,
            'attr' => ['readonly' => true],
            'mapped' => false,
            'data' => $stateName,
        ]);

        $cityName = '';
        if ($city) {
            $cityName = $city->getName();
        }
        $form->add('city', TextType::class, [
            'required' => true,
            'attr' => ['readonly' => true],
            'mapped' => false,
            'data' => $cityName,
        ]);

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

    public static function filterRoles(
        PersonInterface $person,
        FormInterface $form,
        array $roles,
        SecurityHelper $securityHelper
    ) {
        $loggedUserLevel = $securityHelper->getLoggedInUserLevel();
        $targetPersonLevel = $securityHelper->getTargetPersonLevel($person);
        $isLoggedUserSuperAdmin = $securityHelper->isGranted('ROLE_SUPER_ADMIN');

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
        asort($filteredRoles);

        $form->add('roles', ChoiceType::class, [
            'choices' => $filteredRoles,
            'multiple' => true,
            'attr' => ['readonly' => ($targetPersonLevel > $loggedUserLevel)],
            'disabled' => ($targetPersonLevel > $loggedUserLevel),
        ]);

        return $filteredRoles;
    }
}
