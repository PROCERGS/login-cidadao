<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use libphonenumber\PhoneNumberFormat;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Entity\Country;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Entity\City;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManager;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class ProfileFormType extends BaseType
{
    protected $em;
    private $defaultCountryIso2;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $country = $this->em->getRepository('LoginCidadaoCoreBundle:Country')->findOneBy(array(
            'iso2' => $this->defaultCountryIso2));
        $builder
            ->add('email',
                'Symfony\Component\Form\Extension\Core\Type\EmailType',
                array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('firstName',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('label' => 'form.firstName', 'translation_domain' => 'FOSUserBundle'))
            ->add('surname',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('label' => 'form.surname', 'translation_domain' => 'FOSUserBundle'))
            ->add('birthdate',
                'Symfony\Component\Form\Extension\Core\Type\BirthdayType',
                array(
                'required' => false,
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'label' => 'form.birthdate',
                'translation_domain' => 'FOSUserBundle',
                'attr' => array('pattern' => '[0-9/]*', 'class' => 'form-control birthdate')
                )
            )
            ->add('mobile', 'Misd\PhoneNumberBundle\Form\Type\PhoneNumberType',
                array(
                    'required' => false,
                    'label' => 'person.form.mobile.label',
                    'attr' => [
                        'class' => 'form-control intl-tel',
                        'placeholder' => 'person.form.mobile.placeholder',
                    ],
                    'label_attr' => ['class' => 'intl-tel-label'],
                    'format' => PhoneNumberFormat::E164,
                ))
            ->add('image', 'Vich\UploaderBundle\Form\Type\VichFileType',
                array(
                'required' => false,
                'allow_delete' => true, // not mandatory, default is true
                'download_link' => true, // not mandatory, default is true
            ))
            ->add('placeOfBirth',
                'LoginCidadao\CoreBundle\Form\Type\CitySelectorComboType',
                array(
                'level' => 'city',
                'city_label' => 'Place of birth - City',
                'state_label' => 'Place of birth - State',
                'country_label' => 'Place of birth - Country',
            ))
        ;
        $builder->add('nationality',
            'Symfony\Bridge\Doctrine\Form\Type\EntityType',
            array(
            'required' => false,
            'class' => 'LoginCidadaoCoreBundle:Country',
            'choice_label' => 'name',
            'preferred_choices' => array($country),
            'choice_translation_domain' => true,
            'query_builder' => function(EntityRepository $er) {
            return $er->createQueryBuilder('u')
                    ->where('u.reviewed = :reviewed')
                    ->setParameter('reviewed', Country::REVIEWED_OK)
                    ->orderBy('u.name', 'ASC');
        },
            'label' => 'Nationality'
        ));
        $builder->add('defaultCountry',
            'Symfony\Component\Form\Extension\Core\Type\HiddenType',
            array("data" => $country->getId(), "required" => false, "mapped" => false));
    }

    public function getName()
    {
        return 'lc_person_profile';
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }

    public function setDefaultCountryIso2($iso2)
    {
        $this->defaultCountryIso2 = $iso2;
        return $this;
    }
}
