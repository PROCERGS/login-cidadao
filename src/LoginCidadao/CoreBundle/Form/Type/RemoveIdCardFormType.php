<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class RemoveIdCardFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id_card_id', 'hidden')
            ->add('delete', 'submit', array(
                'attr' => array('class' => 'btn btn-danger btn-sm pull-right'),
                'label' => 'Yes, remove')
        );
    }

    public function getName()
    {
        return 'lc_remove_id_card';
    }

}
