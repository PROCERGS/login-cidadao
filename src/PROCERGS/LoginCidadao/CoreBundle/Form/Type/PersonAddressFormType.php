<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
use PROCERGS\LoginCidadao\CoreBundle\Model\SelectData;

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
        $this->translator         = $translator;
        $this->preferredCountries = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Country')
            ->findPreferred();
        $this->preferredStates    = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:State')
            ->findPreferred();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cityEmptyMessage    = $this->translator->trans('No city found.');
        $stateEmptyMessage   = $this->translator->trans('No state found.');
        $countryEmptyMessage = $this->translator->trans('No country found.');

        $builder->add('name', 'text', array('label' => 'address.name'))
            ->add('address', 'text', array('required' => true))
            ->add('addressNumber', 'text', array('required' => false))
            ->add('complement', 'text', array('required' => false))
            ->add('location', 'lc_location', array('level' => 'city'));

        if (isset($this->preferredCountries[0])) {
            $id = $this->preferredCountries[0]->getId();
        } else {
            $id = null;
        }

        $builder->add('preferredcountries', 'hidden',
                array("data" => $id, "required" => false, "mapped" => false))
            ->add('postalCode');

        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
            $data = $event->getData();
            if ($data instanceof PersonAddress) {
                if ($data->getCity()) {
                    $data->setState($data->getCity()->getState());
                    $data->setCountry($data->getCity()->getState()->getCountry());
                } elseif ($data->getLocation() instanceof SelectData) {
                    $data->getLocation()->toObject($data);
                }
            }
        });
        $builder->addEventListener(FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
            $address = $event->getData();
            if ($address instanceof PersonAddress) {
                $address->getLocation()->toObject($address);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
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