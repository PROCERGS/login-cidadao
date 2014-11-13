<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;

class IdCardType extends AbstractType
{

    protected $countryAcronym;

    public function __construct($countryAcronym)
    {
        $this->countryAcronym = $countryAcronym;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countryAcronym = $this->countryAcronym;

        $builder->add('value', 'text',
                      array(
                'required' => true
            ))
            ->add('issuer', 'text',
                  array(
                'required' => true
            ))
            ->add('id', 'hidden',
                  array(
                'required' => false
            ))
            ->add('state', 'entity',
                  array(
                'required' => true,
                'class' => 'PROCERGSLoginCidadaoCoreBundle:State',
                'property' => 'name',
                'query_builder' => function (EntityRepository $er) use ($countryAcronym) {
                    return $er->createQueryBuilder('s')
                        ->join('PROCERGSLoginCidadaoCoreBundle:Country', 'c',
                               'WITH', 's.country = c')
                        ->where('s.reviewed = ' . Country::REVIEWED_OK)
                        ->andWhere('c.iso2 = :country')
                        ->setParameter('country', $countryAcronym)
                        ->orderBy('s.name', 'ASC');
                }
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PROCERGS\LoginCidadao\CoreBundle\Entity\IdCard'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'lc_idcard_form';
    }

}
