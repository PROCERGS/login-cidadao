<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Entity\Country;

class CountrySelectorComboType extends AbstractType
{

    public function getParent()
    {
        return 'entity';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'LoginCidadaoCoreBundle:Country',
            'choice_label' => 'name',
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
