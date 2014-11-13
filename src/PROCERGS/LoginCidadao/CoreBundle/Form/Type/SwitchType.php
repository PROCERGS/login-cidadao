<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\CommonFormType;

class SwitchType extends CommonFormType
{
    public function getParent()
    {
        return 'checkbox';
    }

    public function getName()
    {
        return 'switch';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array(
                'data-enable-switch' => '1',
                'data-off-text' => $this->translator->trans('No'),
                'data-on-text' =>  $this->translator->trans('Yes')
            )
        ));
    }
    
}
