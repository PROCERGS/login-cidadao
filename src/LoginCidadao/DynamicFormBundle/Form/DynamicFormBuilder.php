<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle\Form;

use libphonenumber\PhoneNumberFormat;
use LoginCidadao\CoreBundle\Entity\PersonAddress;
use LoginCidadao\CoreBundle\Model\IdCardInterface;
use LoginCidadao\CoreBundle\Model\LocationSelectData;
use LoginCidadao\DynamicFormBundle\Model\DynamicFormData;
use LoginCidadao\ValidationControlBundle\Handler\ValidationHandler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints;

class DynamicFormBuilder
{
    /** @var ValidationHandler */
    private $validationHandler;

    /**
     * DynamicFormBuilder constructor.
     * @param ValidationHandler $validationHandler
     */
    public function __construct(ValidationHandler $validationHandler)
    {
        $this->validationHandler = $validationHandler;
    }

    public function addFieldFromScope(FormInterface $form, $scope, DynamicFormData $data)
    {
        switch ($scope) {
            case 'name':
            case 'surname':
            case 'full_name':
                $this->addRequiredPersonField($form, 'firstname');
                $this->addRequiredPersonField($form, 'surname');
                break;
            case 'cpf':
                $this->addRequiredPersonField($form, 'cpf', 'form-control cpf');
                break;
            case 'email':
                $this->addRequiredPersonField($form, 'email');
                break;
            case 'id_cards':
                $this->addIdCard($form, $data);
                break;
            case 'phone_number':
            case 'mobile':
                $this->addPhoneField($form);
                break;
            case 'birthdate':
                $this->addBirthdayField($form);
                break;
            case 'city':
            case 'state':
            case 'country':
                $this->addPlaceOfBirth($form, $scope);
                break;
            case 'addresses':
                $this->addAddresses($form, $data);
                break;
            default:
                break;
        }
    }

    private function getPersonForm(FormInterface $form)
    {
        if ($form->has('person') === false) {
            $form->add('person', new DynamicPersonType(), ['label' => false]);
        }

        return $form->get('person');
    }

    private function addRequiredPersonField(FormInterface $form, $field, $cssClass = null)
    {
        $options = ['required' => true];

        if ($cssClass) {
            $options['attr'] = ['class' => $cssClass];
        }

        $this->getPersonForm($form)->add($field, null, $options);
    }

    private function addPhoneField(FormInterface $form)
    {
        $this->getPersonForm($form)->add(
            'mobile',
            'Misd\PhoneNumberBundle\Form\Type\PhoneNumberType',
            [
                'required' => true,
                'label' => 'person.form.mobile.label',
                'attr' => ['class' => 'form-control intl-tel', 'placeholder' => 'person.form.mobile.placeholder'],
                'label_attr' => ['class' => 'intl-tel-label'],
                'format' => PhoneNumberFormat::E164,
            ]
        );
    }

    private function addBirthdayField(FormInterface $form)
    {
        $this->getPersonForm($form)->add(
            'birthdate',
            'birthday',
            [
                'required' => true,
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'label' => 'form.birthdate',
                'translation_domain' => 'FOSUserBundle',
                'attr' => ['pattern' => '[0-9/]*', 'class' => 'form-control birthdate'],
            ]
        );
    }

    private function addPlaceOfBirth(FormInterface $form, $level)
    {
        $form->add(
            'placeOfBirth',
            'LoginCidadao\CoreBundle\Form\Type\CitySelectorComboType',
            [
                'level' => $level,
                'city_label' => 'Place of birth - City',
                'state_label' => 'Place of birth - State',
                'country_label' => 'Place of birth - Country',
                'constraints' => new Constraints\Valid(),
            ]
        );

        return;
    }

    private function addAddresses(FormInterface $form, DynamicFormData $data)
    {
        $address = new PersonAddress();
        $address->setLocation(new LocationSelectData());
        $data->setAddress($address);
        $form->add(
            'address',
            'LoginCidadao\CoreBundle\Form\Type\PersonAddressFormType',
            ['label' => false, 'constraints' => new Constraints\Valid()]
        );
    }

    private function addIdCard(FormInterface $form, DynamicFormData $data)
    {
        $person = $data->getPerson();
        $state = $data->getIdCardState();
        if (!$state) {
            return;
        }
        foreach ($person->getIdCards() as $idCard) {
            if ($idCard->getState()->getId() === $state->getId()) {
                $data->setIdCard($idCard);
                break;
            }
        }

        if (!($data->getIdCard() instanceof IdCardInterface)) {
            $idCard = $this->validationHandler->instantiateIdCard($state);
            $idCard->setPerson($person);
            $data->setIdCard($idCard);
        }

        $form->add('idcard', 'lc_idcard_form', ['label' => false, 'constraints' => new Constraints\Valid()]);
    }
}
