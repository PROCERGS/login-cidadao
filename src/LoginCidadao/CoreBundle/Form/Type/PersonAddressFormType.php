<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use LoginCidadao\CoreBundle\Entity\Country;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Entity\PersonAddress;
use LoginCidadao\CoreBundle\Model\SelectData;

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
        $this->preferredCountries = $em->getRepository('LoginCidadaoCoreBundle:Country')
            ->findPreferred();
        $this->preferredStates    = $em->getRepository('LoginCidadaoCoreBundle:State')
            ->findPreferred();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cityEmptyMessage    = $this->translator->trans('No city found.');
        $stateEmptyMessage   = $this->translator->trans('No state found.');
        $countryEmptyMessage = $this->translator->trans('No country found.');

        $builder->add('name',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('label' => 'address.name'))
            ->add('address',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => true))
            ->add('addressNumber',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('complement',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('location',
                'LoginCidadao\CoreBundle\Form\Type\CitySelectorComboType',
                array('level' => 'city'));

        if (isset($this->preferredCountries[0])) {
            $id = $this->preferredCountries[0]->getId();
        } else {
            $id = null;
        }

        $builder->add('preferredcountries',
                'Symfony\Component\Form\Extension\Core\Type\HiddenType',
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
            'data_class' => 'LoginCidadao\CoreBundle\Entity\PersonAddress'
        ));
    }
}
