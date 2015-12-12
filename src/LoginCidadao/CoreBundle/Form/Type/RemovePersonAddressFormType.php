<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class RemovePersonAddressFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address_id', 'hidden')
            ->add('delete', 'submit', array(
                'attr' => array('class' => 'btn btn-danger btn-xs'),
                'label' => 'Yes, remove')
        );
    }

    public function getName()
    {
        return 'lc_remove_person_address';
    }

}
