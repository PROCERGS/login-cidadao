<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumberFormat;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Entity\Country;

class ProfileFormType extends BaseType
{
    private $em;
    private $defaultCountryIso2;

    /**
     * @param string $class The User class name
     * @param EntityManagerInterface $em
     * @param $defaultCountryIso2
     */
    public function __construct($class, EntityManagerInterface $em, $defaultCountryIso2)
    {
        parent::__construct($class);
        $this->em = $em;
        $this->defaultCountryIso2 = $defaultCountryIso2;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $country = $this->em->getRepository('LoginCidadaoCoreBundle:Country')->findOneBy(array(
            'iso2' => $this->defaultCountryIso2,
        ));

        $emailType = 'Symfony\Component\Form\Extension\Core\Type\EmailType';
        $textType = 'Symfony\Component\Form\Extension\Core\Type\TextType';

        $builder
            ->add('email', $emailType, ['label' => 'form.email', 'translation_domain' => 'FOSUserBundle'])
            ->add('firstName', $textType, ['label' => 'form.firstName', 'translation_domain' => 'FOSUserBundle'])
            ->add('surname', $textType, ['label' => 'form.surname', 'translation_domain' => 'FOSUserBundle'])
            ->add('birthdate',
                'Symfony\Component\Form\Extension\Core\Type\BirthdayType',
                [
                    'required' => false,
                    'format' => 'dd/MM/yyyy',
                    'widget' => 'single_text',
                    'label' => 'form.birthdate',
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => ['pattern' => '[0-9/]*', 'class' => 'form-control birthdate'],
                ])
            ->add('mobile', 'Misd\PhoneNumberBundle\Form\Type\PhoneNumberType',
                [
                    'required' => false,
                    'label' => 'person.form.mobile.label',
                    'attr' => [
                        'class' => 'form-control intl-tel',
                        'placeholder' => 'person.form.mobile.placeholder',
                    ],
                    'label_attr' => ['class' => 'intl-tel-label'],
                    'format' => PhoneNumberFormat::E164,
                ])
            ->add('image', 'Vich\UploaderBundle\Form\Type\VichFileType',
                [
                    'required' => false,
                    'allow_delete' => true, // not mandatory, default is true
                    'download_link' => true,// not mandatory, default is true
                ])
            ->add('placeOfBirth',
                'LoginCidadao\CoreBundle\Form\Type\CitySelectorComboType',
                [
                    'required' => false,
                    'level' => 'city',
                    'city_label' => 'Place of birth - City',
                    'state_label' => 'Place of birth - State',
                    'country_label' => 'Place of birth - Country',
                ]);
        $builder->add('nationality',
            'Symfony\Bridge\Doctrine\Form\Type\EntityType',
            [
                'required' => false,
                'class' => 'LoginCidadaoCoreBundle:Country',
                'choice_label' => 'name',
                'preferred_choices' => [$country],
                'choice_translation_domain' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.reviewed = :reviewed')
                        ->setParameter('reviewed', Country::REVIEWED_OK)
                        ->orderBy('u.name', 'ASC');
                },
                'label' => 'Nationality',
            ]);

        $builder->add('defaultCountry',
            'Symfony\Component\Form\Extension\Core\Type\HiddenType',
            ["data" => $country->getId(), "required" => false, "mapped" => false]
        );
    }

    public function getName()
    {
        return 'lc_person_profile';
    }
}
