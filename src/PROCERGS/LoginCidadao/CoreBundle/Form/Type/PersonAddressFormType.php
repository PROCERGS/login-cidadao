<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
use PROCERGS\LoginCidadao\CoreBundle\Form\DataTransformer\StateToStringTransformer;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonAddress;

class PersonAddressFormType extends AbstractType
{

    protected $translator;

    /**
     * @var array
     */
    protected $preferredCountries;

    /**
     * @var array
     */
    protected $preferredStates;

    public function __construct(TranslatorInterface $translator,
                                EntityManager $em)
    {
        $this->translator = $translator;
        $this->preferredCountries = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Country')
            ->findPreferred();
        $this->preferredStates = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:State')
            ->findPreferred();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cityEmptyMessage = $this->translator->trans('No city found.');
        $stateEmptyMessage = $this->translator->trans('No state found.');
        $countryEmptyMessage = $this->translator->trans('No country found.');

        $builder->add('name');
        $builder->add('address', 'text', array('required' => true));
        $builder->add('addressNumber', 'text', array('required' => false));
        $builder->add('complement', 'text', array('required' => false));
        $builder->add('country', 'entity',array(
            'required' => false,
            'class' => 'PROCERGSLoginCidadaoCoreBundle:Country',
            'property' => 'name',
            'preferred_choices' => $this->preferredCountries,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('u')
                ->where('u.reviewed = :reviewed')
                ->setParameter('reviewed', Country::REVIEWED_OK)
                ->orderBy('u.name', 'ASC');
            },
            'attr' => array(
                'class' => 'form-control countries'
            )
        ));
        $builder->add('statesteppe', 'text', array("required"=> false, "mapped"=>false));
        $builder->add('citysteppe', 'text', array("required"=> false, "mapped"=>false));
        if (isset($this->preferredCountries[0])) {
            $id = $this->preferredCountries[0]->getId();
        } else {
            $id = null;
        }
        $builder->add('preferredcountries', 'hidden', array("data" => $id,"required"=> false, "mapped"=>false));        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            if ($data instanceof PersonAddress) {
                if ($data->getCity()) {
                    $data->setState($data->getCity()->getState());
                    $data->setCountry($data->getCity()->getState()->getCountry());
                }
            }
            $form->add('state', 'entity',array(
                'required' => false,
                'class' => 'PROCERGSLoginCidadaoCoreBundle:State',
                'property' => 'name',
                'empty_value' => '',
                'query_builder' => function(EntityRepository $er) use ($data) {
                    if ($data instanceof PersonAddress) {
                        $state = $data->getState();
                        $country = $data->getCountry();
                    } else {
                        $country = null;
                        $state = null;
                    }

                    $params = array(
                        'reviewed' => State::REVIEWED_OK,
                        'country' => $country instanceof Country ? $country : null,
                        'state' => $state instanceof State ? $state : null
                    );

                    return $er->createQueryBuilder('s')
                        ->where('s.reviewed = :reviewed')
                        ->andWhere('s.country = :country')
                        ->orWhere('s = :state')
                        ->setParameters($params)
                        ->orderBy('s.name', 'ASC');
                }
            ));
            $form->add('city', 'entity',array(
                'required' => false,
                'class' => 'PROCERGSLoginCidadaoCoreBundle:City',
                'property' => 'name',
                'empty_value' => '',
                'query_builder' => function(EntityRepository $er) use ($data) {
                    if ($data instanceof PersonAddress) {
                        $city = $data->getCity();
                        $state = $data->getState();
                        $country = $data->getCountry();
                    } else {
                        $country = null;
                        $state = null;
                        $city = null;
                    }
                    $params = array(
                        'reviewed' => State::REVIEWED_OK,
                        'state' => ($country instanceof Country && $state instanceof State) ? $state : null,
                        'city' => ($country instanceof Country && $state instanceof State && $city instanceof City) ? $city : null
                    );
                    return $er->createQueryBuilder('c')
                        ->where('c.reviewed = :reviewed')
                        ->andWhere('c.state = :state')
                        ->orWhere('c = :city')
                        ->setParameters($params)
                        ->orderBy('c.name', 'ASC');
                }
            ));

        });
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            $form->add('state', 'entity',array(
                'required' => false,
                'class' => 'PROCERGSLoginCidadaoCoreBundle:State',
                'property' => 'name',
                'empty_value' => '',
                'query_builder' => function(EntityRepository $er) use ($data) {
                    $params = array(
                        'reviewed' => State::REVIEWED_OK,
                        'country' => isset($data['country']) && is_numeric($data['country']) ? $data['country'] : null,
                        'stateId' => isset($data['state']) && is_numeric($data['state']) ? $data['state'] : null
                    );
                    return $er->createQueryBuilder('s')
                            ->where('s.reviewed = :reviewed')
                            ->andWhere('s.country = :country')
                            ->orWhere('s.id = :stateId')
                            ->setParameters($params)
                            ->orderBy('s.name', 'ASC');
                }
            ));
            $form->add('city', 'entity',array(
                'required' => false,
                'class' => 'PROCERGSLoginCidadaoCoreBundle:City',
                'property' => 'name',
                'empty_value' => '',
                'query_builder' => function(EntityRepository $er) use ($data) {
                    $params = array(
                        'reviewed' => State::REVIEWED_OK,
                        'state' => isset($data['state']) && is_numeric($data['state']) ? $data['state'] : null,
                        'cityId' => isset($data['city']) && is_numeric($data['city']) ? $data['city'] : null
                    );
                    return $er->createQueryBuilder('c')
                                ->where('c.reviewed = :reviewed')
                                ->andWhere('c.state = :state')
                                ->orWhere('c.id = :cityId')
                                ->setParameters($params)
                                ->orderBy('c.name', 'ASC');
                }
            ));

        });
        
            $builder
            ->add('postalCode')
            ->add('save', 'submit',
                  array('attr' => array('class' => 'btn btn-success')));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PROCERGS\LoginCidadao\CoreBundle\Entity\PersonAddress'
        ));
    }

    public function getName()
    {
        return 'lc_person_address';
    }

}
