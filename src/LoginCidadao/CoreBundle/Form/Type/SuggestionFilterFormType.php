<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuggestionFilterFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username',
            'Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
            'required' => false,
            'label' => 'sugg.username'
        ));
        $builder->add('dateini',
            'Symfony\Component\Form\Extension\Core\Type\DateType',
            array(
            'required' => false,
            'format' => 'dd/MM/yyyy',
            'widget' => 'single_text',
            'years' => range(date('Y'), 1898),
            'label' => 'sugg.dateini',
            'attr' => array('pattern' => '[0-9/]*', 'class' => 'date')
        ));
        $builder->add('dateend',
            'Symfony\Component\Form\Extension\Core\Type\DateType',
            array(
            'required' => false,
            'format' => 'dd/MM/yyyy',
            'widget' => 'single_text',
            'years' => range(date('Y'), 1898),
            'label' => 'sugg.dateend',
            'attr' => array('pattern' => '[0-9/]*', 'class' => 'date')
        ));
        $builder->add('text',
            'Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
            'required' => false,
            'label' => 'sugg.text'
        ));
        $builder->setMethod('GET');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}
