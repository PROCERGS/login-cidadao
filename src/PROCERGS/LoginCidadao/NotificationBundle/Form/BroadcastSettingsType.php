<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\CategoryRepository;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonRepository;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\PlaceholderRepository;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Category;

class BroadcastSettingsType extends AbstractType
{

    private $broadcastId;
    private $categoryId;

    public function __construct($broadcastId, $categoryId)
    {
        $this->broadcastId = $broadcastId;
        $this->categoryId = $categoryId;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $broadcastId = $this->broadcastId;
        $categoryId = $this->categoryId;

        $builder
            ->add('placeholders', 'collection',
                   array(
                    'type' => new BroadcastPlaceholderType()
            ))
            ->add('title', 'text', array("required"=> true))
            ->add('shortText', 'text', array("required"=> true))            
            ->add('save', 'submit', array('label' => 'Save'))
            ->add('saveAndAdd', 'submit', array('label' => 'Save and Send'))
            ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PROCERGS\LoginCidadao\NotificationBundle\Model\BroadcastSettings'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'broadcast';
    }

}
