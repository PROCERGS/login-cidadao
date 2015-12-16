<?php

namespace LoginCidadao\CoreBundle\Form\Type;

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
            ->add('email', 'email',
                array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('firstName', 'text',
                array('label' => 'form.firstName', 'translation_domain' => 'FOSUserBundle'))
            ->add('surname', 'text',
                array('label' => 'form.surname', 'translation_domain' => 'FOSUserBundle'))
            ->add('birthdate', 'birthday',
                array(
                'required' => false,
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'label' => 'form.birthdate',
                'translation_domain' => 'FOSUserBundle',
                'attr' => array('pattern' => '[0-9/]*', 'class' => 'form-control birthdate')
                )
            )
            ->add('mobile', null,
                array('required' => false, 'label' => 'form.mobile', 'translation_domain' => 'FOSUserBundle'))
            ->add('image', 'vich_file',
                array(
                'required' => false,
                'allow_delete' => true, // not mandatory, default is true
                'download_link' => true, // not mandatory, default is true
            ))
            ->add('placeOfBirth', 'lc_location',
                array(
                'level' => 'city',
                'city_label' => 'Place of birth - City',
                'state_label' => 'Place of birth - State',
                'country_label' => 'Place of birth - Country',
            ))
        ;
        $builder->add('nationality', 'entity',
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
        $builder->add('defaultCountry', 'hidden',
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
