<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\CommonFormType;

class AjaxChoiceType extends CommonFormType
{

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'ajax_choice';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        /*
        $resolver->setDefaults(array(
            'route' => 'a',
            'search_prop' => 'a',
            'extra_prop' =>  'a',
        ));
        */
    }
}
