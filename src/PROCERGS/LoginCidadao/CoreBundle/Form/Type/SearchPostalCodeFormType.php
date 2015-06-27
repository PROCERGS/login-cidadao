<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

class SearchPostalCodeFormType extends AbstractType
{

    private $em;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $queryBuilder = function (EntityRepository $er) {
            return $er->createQueryBuilder('u')->orderBy('u.acronym');
        };
        $stateRepo = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:State');
        $preferredChoices = $stateRepo->findBy(array('acronym' => 'RS'));
        $builder->add('adress', 'text',
                array(
            'required' => true,
            'label' => 'form.adress',
            'translation_domain' => 'FOSUserBundle'
        ))->add('adressnumber', 'text',
                array(
            'required' => false,
            'label' => 'form.adressnumber',
            'translation_domain' => 'FOSUserBundle'
        ))->add('city', 'text',
                array(
            'required' => true,
            'label' => 'form.city',
            'translation_domain' => 'FOSUserBundle'
        ))->add('state', 'entity',
                array(
            'class' => 'PROCERGSLoginCidadaoCoreBundle:State',
            'choice_label' => 'name',
            'required' => true,
            'label' => 'form.state',
            'preferred_choices' => $preferredChoices,
            'query_builder' => $queryBuilder,
            'translation_domain' => 'FOSUserBundle'
        ));
    }

    public function getName()
    {
        return 'search_postalcode_form_type';
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }

}
