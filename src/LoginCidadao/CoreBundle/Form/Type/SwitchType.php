<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use LoginCidadao\CoreBundle\Form\Type\CommonFormType;

class SwitchType extends CommonFormType
{

    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\CheckboxType';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array(
                'data-enable-switch' => '1',
                'data-off-text' => $this->translator->trans('No'),
                'data-on-text' => $this->translator->trans('Yes')
            )
        ));
    }
}
