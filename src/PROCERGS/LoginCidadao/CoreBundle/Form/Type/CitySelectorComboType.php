<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Model\SelectData;
use PROCERGS\LoginCidadao\CoreBundle\Model\Manager\CityManager;
use PROCERGS\LoginCidadao\CoreBundle\Model\Manager\StateManager;
use PROCERGS\LoginCidadao\CoreBundle\Model\Manager\CountryManager;
use PROCERGS\LoginCidadao\CoreBundle\EventListener\ProfileEditListner;

class CitySelectorComboType extends AbstractType
{
    /** @var CityManager */
    private $cityManager;

    /** @var StateManager */
    private $stateManager;

    /** @var CountryManager */
    private $countryManager;

    /** @var ProfileEditListner */
    private $profileEditSubscriber;

    /** @var TranslatorInterface */
    private $translator;

    /** @var string */
    private $level;

    public function __construct(CountryManager $countryManager,
                                StateManager $stateManager,
                                CityManager $cityManager,
                                ProfileEditListner $profileEditSubscriber,
                                TranslatorInterface $translator)
    {
        $this->translator            = $translator;
        $this->cityManager           = $cityManager;
        $this->stateManager          = $stateManager;
        $this->countryManager        = $countryManager;
        $this->profileEditSubscriber = $profileEditSubscriber;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('country', 'entity',
            array(
            'empty_value' => '',
            'class' => $this->countryManager->getClass(),
            'choice_label' => 'name',
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
            if ($countryId === null) {
                $choices = array();
            } else {
                $choices = $stateManager->findByCountryId($countryId);
            }
            if (empty($choices)) {
                if ($form->has('state')) {
                    $form->remove('state');
                }
                $form->add('state_text', (empty($choices) ? 'text' : 'hidden'),
                    array(
                    'label' => $options['state_label'],
                    //'mapped' => false,
                    'attr' => array(
                        'class' => 'form-control location-select state-select location-text'
                    )
                ));
                return;
            } else {
                if ($form->has('state_text')) {
                    $form->remove('state_text');
                }
            }
            $form->add('state', 'entity',
                array(
                'class' => $stateManager->getClass(),
                'choice_label' => 'name',
                'empty_value' => '',
                'choices' => $choices,
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
            if ($stateId === null) {
                $choices = array();
            } else {
                $choices = $cityManager->findByStateId($stateId);
            }
            if (empty($choices)) {
                if ($form->has('city')) {
                    $form->remove('city');
                }
                $form->add('city_text', 'text',
                    array(
                    'label' => $options['city_label'],
                    //'mapped' => false,
                    'attr' => array(
                        'class' => 'form-control location-select city-select location-text'
                    ))
                );
                return;
            } else {
                if ($form->has('city_text')) {
                    $form->remove('city_text');
                }
            }
            $form->add('city', 'entity',
                array(
                'class' => $cityManager->getClass(),
                'empty_value' => '',
                'choice_label' => 'name',
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
            $hasState     = isset($data['state']) && !empty($data['state']);
            $hasStateText = isset($data['state_text']) && !empty($data['state_text']);
            if ($level !== 'country' && $hasState) {
                $refreshCity($form, $data['state']);
            }
            if ($level !== 'country' && $hasStateText) {
                $refreshCity($form, $data['state_text']);
            }
        });

        $builder->addEventSubscriber($this->profileEditSubscriber);
    }

    public function configureOptions(OptionsResolver $resolver)
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

    public function finishView(FormView $view, FormInterface $form,
                               array $options)
    {
        $collator     = new \Collator($this->translator->getLocale());
        $translator   = $this->translator;
        $sortFunction = function ($a, $b) use ($collator, $translator) {
            return $collator->compare($translator->trans($a->label),
                    $translator->trans($b->label));
        };
        usort($view->children['country']->vars['choices'], $sortFunction);
        if (array_key_exists('state', $view->children)) {
            usort($view->children['state']->vars['choices'], $sortFunction);
        }
        if (array_key_exists('city', $view->children)) {
            usort($view->children['city']->vars['choices'], $sortFunction);
        }
    }
}
