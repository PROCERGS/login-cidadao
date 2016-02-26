<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class RemoveIdCardFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id_card_id',
                'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('delete',
                'Symfony\Component\Form\Extension\Core\Type\SubmitType',
                array(
                'attr' => array('class' => 'btn btn-danger btn-sm pull-right'),
                'label' => 'Yes, remove')
        );
    }
}
