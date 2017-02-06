<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use libphonenumber\PhoneNumberFormat;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\HttpFoundation\Session\Session;

class RegistrationFormType extends BaseType
{

    /** @var Session */
    protected $session;

    /**
     * @param string $class The User class name
     * @param Session $session
     */
    public function __construct($class, Session $session)
    {
        parent::__construct($class);
        $this->session = $session;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                'Symfony\Component\Form\Extension\Core\Type\EmailType',
                array(
                    'required' => true,
                    'label' => 'form.email',
                    'attr' => array('placeholder' => 'form.email.example'),
                    'translation_domain' => 'FOSUserBundle',
                )
            )
            ->add(
                'plainPassword',
                'Symfony\Component\Form\Extension\Core\Type\RepeatedType',
                array(
                    'required' => true,
                    'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
                    'attr' => array(
                        'autocomplete' => 'off',
                        'placeholder' => 'form.plainPassword.example',
                    ),
                    'options' => array('translation_domain' => 'FOSUserBundle'),
                    'first_options' => array(
                        'label' => 'form.password',
                        'attr' => array('placeholder' => 'form.plainPassword.example'),
                    ),
                    'second_options' => array(
                        'label' => 'form.password_confirmation',
                        'attr' => array('placeholder' => 'form.plainPassword.confirm.example'),
                    ),
                    'invalid_message' => 'fos_user.password.mismatch',
                )
            );

        if ($this->session->has('requested_scope')) {
            $builder
                ->add(
                    'firstName',
                    null,
                    [
                        'required' => true,
                        'label' => 'person.form.firstName.label',
                        'attr' => [
                            'placeholder' => 'person.form.firstName.placeholder',
                        ],
                    ]
                );
            $requestedScope = explode(' ', $this->session->get('requested_scope'));
            foreach ($requestedScope as $scope) {
                $this->addDynamicField($builder, $scope);
            }
        }
    }

    public function getBlockPrefix()
    {
        return 'lc_person_registration';
    }

    private function addDynamicField(FormBuilderInterface $builder, $scope)
    {
        switch ($scope) {
            case 'name':
            case 'surname':
            case 'full_name':
                $builder->add(
                    'surname',
                    null,
                    [
                        'required' => true,
                        'attr' => [
                            'placeholder' => 'person.form.surname.placeholder',
                        ],
                    ]
                );
                break;
            case 'cpf':
                $builder->add(
                    'cpf',
                    'LoginCidadao\CoreBundle\Form\Type\TelType',
                    [
                        'required' => false,
                        'attr' => [
                            'class' => 'form-control cpf',
                            'placeholder' => 'person.form.cpf.placeholder',
                            'maxlength' => 14,
                        ],
                    ]
                );
                break;
            case 'mobile':
            case 'phone_number':
                $builder->add(
                    'mobile',
                    'Misd\PhoneNumberBundle\Form\Type\PhoneNumberType',
                    [
                        'required' => false,
                        'label_attr' => ['class' => 'intl-tel-label'],
                        'attr' => [
                            'placeholder' => 'person.form.mobile.placeholder',
                            'class' => 'form-control intl-tel',
                        ],
                        'format' => PhoneNumberFormat::E164,
                    ]
                );
                break;
            case 'birthdate':
                $builder->add(
                    'birthdate',
                    'LoginCidadao\CoreBundle\Form\Type\BirthdayTelType',
                    [
                        'required' => true,
                        'format' => 'dd/MM/yyyy',
                        'widget' => 'single_text',
                        'label' => 'form.birthdate',
                        'translation_domain' => 'FOSUserBundle',
                        'attr' => [
                            'type' => 'tel',
                            'class' => 'form-control birthdate',
                            'placeholder' => 'person.form.birthdate.placeholder',
                        ],
                    ]
                );
                break;
            default:
                break;
        }
    }
}
