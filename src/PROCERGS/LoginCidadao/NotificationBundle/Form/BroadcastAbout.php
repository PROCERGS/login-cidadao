<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\CategoryRepository;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonRepository;

class BroadcastAbout extends AbstractType
{

    private $broadcastId;    

    public function __construct($broadcastId)
    {
        $this->broadcastId = $broadcastId;        
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('save', 'submit', array('label' => 'Send notifications'))          
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'broadcast_settings';
    }

}
