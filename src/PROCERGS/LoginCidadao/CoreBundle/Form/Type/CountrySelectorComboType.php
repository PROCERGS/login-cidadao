<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;

class CountrySelectorComboType extends AbstractType
{

    public function getParent()
    {
        return 'entity';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'PROCERGSLoginCidadaoCoreBundle:Country',
            'property' => 'name',
            'empty_value' => '',
            'query_builder' => $this->getFilterFunction(),
            'label' => 'Place of birth - Country',
            'attr' => array(
                'class' => 'form-control location-select country-select'
            )
        ));
    }

    public function getName()
    {
        return 'country_selector_combo';
    }

    private function getFilterFunction()
    {
        return function(EntityRepository $er) {
            $params = array(
                'reviewed' => Country::REVIEWED_OK
            );
            return $er->createQueryBuilder('c')
                    ->where('c.reviewed = :reviewed')
                    ->setParameters($params)
                    ->orderBy('c.name', 'ASC');
        };
    }
}
