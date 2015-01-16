<?php

namespace PROCERGS\LoginCidadao\IgpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use PROCERGS\LoginCidadao\IgpBundle\Validator\Constraints\RG;

class IgpIdCardFormType extends AbstractType
{

    /**
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nomeCI', 'text', array(
            'required' => true,
            'label' => 'nomeCI',
        ));
        $builder->add('dataEmissaoCI', 'birthday', array(
            'required' => true,
            'format' => 'dd/MM/yyyy',
            'widget' => 'single_text',
            'attr' => array(
                'pattern' => '[0-9/]*'
            ),
            'label' => 'dataEmissaoCI',
        ));
        $builder->add('nomeMae', 'text', array(
            'required' => true,
            'label' => 'nomeMae',
        ));
    }

    /**
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PROCERGS\LoginCidadao\IgpBundle\Entity\IgpIdCard'
        ));
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'lc_igpidcardformtype';
    }
}
