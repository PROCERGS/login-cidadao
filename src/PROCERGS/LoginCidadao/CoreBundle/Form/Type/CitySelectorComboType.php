<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;

class CitySelectorComboType extends AbstractType
{
    /** @var State */
    protected $state;

    public function __construct(State $state = null)
    {
        $this->state = $state;
    }

    public function getParent()
    {
        return 'entity';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $state = $this->state;

        $resolver->setDefaults(array(
            'class' => 'PROCERGSLoginCidadaoCoreBundle:City',
            'property' => 'name',
            'empty_value' => '',
            'query_builder' => $this->getFilterFunction($state),
            'label' => 'Place of birth - City',
            'attr' => array(
                'class' => 'form-control location-select city-select'
            )
        ));
    }

    public function getName()
    {
        return 'city_selector_combo';
    }

    private function getFilterFunction(State $state = null)
    {
        return function(EntityRepository $er) use ($state) {
            $params = array(
                'reviewed' => State::REVIEWED_OK,
                'state' => ($state instanceof State) ? $state : null
            );
            return $er->createQueryBuilder('c')
                    ->where('c.reviewed = :reviewed and c.state = :state')
                    ->setParameters($params)
                    ->orderBy('c.name', 'ASC');
        };
    }
}
