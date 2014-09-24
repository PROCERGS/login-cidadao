<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

// use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HiddenEntityType extends AbstractType
{

    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'hidden_entity';
    }
}
