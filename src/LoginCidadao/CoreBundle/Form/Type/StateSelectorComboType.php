<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Entity\Country;

class StateSelectorComboType extends AbstractType
{
    /** @var Country */
    protected $country;

    public function __construct(Country $country = null)
    {
        $this->country = $country;
    }

    public function getParent()
    {
        return 'entity';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $country = $this->country;

        $resolver->setDefaults(array(
            'invalid_message' => 'The selected state was not found',
            'class' => 'LoginCidadaoCoreBundle:State',
            'choice_label' => 'name',
            'empty_value' => '',
            'query_builder' => $this->getFilterFunction($country),
            'label' => 'Place of birth - State',
            'attr' => array(
                'class' => 'form-control location-select state-select'
            )
        ));
    }

    public function getName()
    {
        return 'state_selector_combo';
    }

    private function getFilterFunction(Country $country)
    {
        return function(EntityRepository $er) use ($country) {
            $params = array(
                'reviewed' => Country::REVIEWED_OK,
                'country' => ($country instanceof Country) ? $country : null
            );
            return $er->createQueryBuilder('s')
                    ->where('s.reviewed = :reviewed and s.country = :country')
                    ->setParameters($params)
                    ->orderBy('s.name', 'ASC');
        };
    }
}
