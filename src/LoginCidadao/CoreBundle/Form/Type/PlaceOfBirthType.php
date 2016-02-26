<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Entity\State;

class PlaceOfBirthType extends AbstractType
{
    /** @var string */
    protected $level;

    public function __construct($level)
    {
        $this->level = $level;
    }

    public function getParent()
    {
        return 'form';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
            $form   = $event->getForm()->getParent();
            $person = $form->getData();

            $form->add('country', new CountrySelectorComboType());
            $form->add('state',
                new StateSelectorComboType($person->getCountry()));
            $form->add('city', new CitySelectorComboType($person->getState()));
        });
        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm()->getParent();

            $form->add('country', new CountrySelectorComboType());
            $form->add('state', new StateSelectorComboType($data['country']));
            $form->add('city', new CitySelectorComboType($data['state']));
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(array(
            'property_path' => false
        ));
    }

    public function getName()
    {
        return 'place_of_birth';
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
