<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
use PROCERGS\LoginCidadao\CoreBundle\Form\DataTransformer\StateToStringTransformer;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;
use Doctrine\ORM\EntityManager;

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

        $builder
            ->add('name', null,
                  array('attr' => array('class' => 'form-control')))
            ->add('line1', null,
                  array('attr' => array('class' => 'form-control'), 'label' => 'Address'))
            ->add('line2', null,
                  array('attr' => array('class' => 'form-control'), 'label' => 'Address Second Line', 'required' => false))
            ->add('country', 'entity',
                  array(
                'class' => 'PROCERGSLoginCidadaoCoreBundle:Country',
                'property' => 'name',
                'preferred_choices' => $this->preferredCountries,
                'attr' => array(
                    'class' => 'form-control countries'
                ), 'required' => false))
            ->add('state', 'entity',
                  array(
                'class' => 'PROCERGSLoginCidadaoCoreBundle:State',
                'property' => 'name',
                'preferred_choices' => $this->preferredStates,
                'attr' => array(
                    'class' => 'form-control states',
                ), 'required' => false))
            ->add('city', 'city_selector',
                  array('attr' => array('class' => 'form-control city-selector', 'data-empty-message' => $cityEmptyMessage, 'data-selector' => 'city'), 'required' => false))
            ->add('postalCode', null,
                  array('attr' => array('class' => 'form-control')))
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
