<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use PROCERGS\LoginCidadao\ValidationControlBundle\ValidationEvents;

class IdCardType extends AbstractType
{

    protected $countryAcronym;

    public function __construct($countryAcronym,
                                ContainerAwareEventDispatcher $dispatcher)
    {
        $this->countryAcronym = $countryAcronym;
        $this->dispatcher = $dispatcher;
    }

    /**
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countryAcronym = $this->countryAcronym;
        $builder->add('id', 'hidden',
                      array(
            'required' => false
        ));
        $builder->add('state', 'entity',
                      array(
            'required' => true,
            'class' => 'PROCERGSLoginCidadaoCoreBundle:State',
            'property' => 'name',
            'read_only' => true,
            'query_builder' => function (EntityRepository $er) use($countryAcronym) {
                return $er->createQueryBuilder('s')
                        ->join('PROCERGSLoginCidadaoCoreBundle:Country', 'c',
                               'WITH', 's.country = c')
                        ->where('s.reviewed = ' . Country::REVIEWED_OK)
                        ->andWhere('c.iso2 = :country')
                        ->setParameter('country', $countryAcronym)
                        ->orderBy('s.name', 'ASC');
            }
        ));
        $builder->add('issuer', 'text',
                      array(
            'required' => true
        ));
        $builder->add('value', 'text',
                      array(
            'required' => true,
            'label' => 'Idcard value'
        ));

        $dispatcher = $this->dispatcher;
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
                                   function (FormEvent $event) use ($dispatcher) {
            $dispatcher->dispatch(ValidationEvents::ID_CARD_FORM_PRE_SET_DATA,
                                  $event);
        });
        $builder->addEventListener(FormEvents::PRE_SUBMIT,
                                   function (FormEvent $event) use ($dispatcher) {
            $dispatcher->dispatch(ValidationEvents::ID_CARD_FORM_PRE_SUBMIT,
                                  $event);
        });
    }

    /**
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PROCERGS\LoginCidadao\CoreBundle\Entity\IdCard'
        ));
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'lc_idcard_form';
    }

}
