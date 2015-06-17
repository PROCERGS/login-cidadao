<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Model\SelectData;
use PROCERGS\LoginCidadao\CoreBundle\Model\Manager\CityManager;
use PROCERGS\LoginCidadao\CoreBundle\Model\Manager\StateManager;
use PROCERGS\LoginCidadao\CoreBundle\Model\Manager\CountryManager;

class CitySelectorComboType extends AbstractType
{
    /** @var CityManager */
    private $cityManager;

    /** @var StateManager */
    private $stateManager;

    /** @var CountryManager */
    private $countryManager;

    /** @var string */
    private $level;

    public function __construct(CountryManager $countryManager,
                                StateManager $stateManager,
                                CityManager $cityManager)
    {
        $this->cityManager    = $cityManager;
        $this->stateManager   = $stateManager;
        $this->countryManager = $countryManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('country', 'entity',
            array(
            'empty_value' => '',
            'class' => $this->countryManager->getClass(),
            'property' => 'name',
            'choices' => $this->countryManager->findAll(),
            'attr' => array(
                'class' => 'form-control location-select country-select'
            ),
            'label' => $options['country_label']
        ));

        $stateManager = $this->stateManager;
        $cityManager  = $this->cityManager;
        $level        = $options['level'];

        $refreshState = function (FormInterface $form, $countryId = null) use ($options, $stateManager, $level) {
            if ($level === 'country') {
                return;
            }
            $form->add('state', 'entity',
                array(
                'class' => $stateManager->getClass(),
                'property' => 'name',
                'empty_value' => '',
                'choices' => $countryId === null ? array() : $stateManager->findByCountryId($countryId),
                'attr' => array(
                    'class' => 'form-control location-select state-select'
                ),
                'label' => $options['state_label']
            ));
        };

        $refreshCity = function (FormInterface $form, $stateId = null) use ($options, $cityManager, $level) {
            if ($level === 'country' || $level === 'state') {
                return;
            }
            $form->add('city', 'entity',
                array(
                'class' => $cityManager->getClass(),
                'empty_value' => '',
                'property' => 'name',
                'choices' => $stateId === null ? array() : $cityManager->findByStateId($stateId),
                'attr' => array(
                    'class' => 'form-control location-select city-select'
                ),
                'label' => $options['city_label']
            ));
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($refreshState, $refreshCity, $level) {
            $form = $event->getForm();
            $data = $event->getData();

            $countryId = null;
            $stateId   = null;

            if ($data instanceof SelectData) {
                if ($data->getCountry() !== null && $data->getCountry()->getId()
                    !== null) {
                    $countryId = $data->getCountry()->getId();
                }
                if ($level !== 'country' && $data->getState() !== null && $data->getState()->getId()
                    !== null) {
                    $stateId = $data->getState()->getId();
                }
            }

            $refreshState($form, $countryId, $level);
            $refreshCity($form, $stateId, $level);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($refreshState, $refreshCity, $level) {
            $form = $event->getForm();
            $data = $event->getData();

            if (isset($data['country']) && !empty($data['country'])) {
                $refreshState($form, $data['country']);
            }
            if ($level !== 'country' && isset($data['state']) && !empty($data['state'])) {
                $refreshCity($form, $data['state']);
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PROCERGS\LoginCidadao\CoreBundle\Model\SelectData',
            'label' => false,
            'level' => 'city',
            'country_label' => 'Country',
            'state_label' => 'State',
            'city_label' => 'City'
        ));
    }

    public function getName()
    {
        return 'lc_location';
    }
}
