<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Notification;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NotificationType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('icon')
                ->add('title')
                ->add('shortText')
                ->add('text')
                ->add('callbackUrl')
                ->add('createdAt', 'datetime',
                        array('required' => false, 'widget' => 'single_text'))
                ->add('readDate', 'datetime',
                        array('required' => false, 'widget' => 'single_text'))
                ->add('isRead')
                ->add('person', 'entity',
                        array(
                    'class' => 'PROCERGSLoginCidadaoCoreBundle:Person',
                    'property' => 'id'
                ))
                ->add('sender', 'entity',
                        array(
                    'class' => 'PROCERGSOAuthBundle:Client',
                    'property' => 'randomId'
                ))
                ->add('expireDate', 'datetime',
                        array('required' => false, 'widget' => 'single_text'))
                ->add('considerReadDate', 'datetime',
                        array('required' => false, 'widget' => 'single_text'))
                ->add('receivedDate', 'datetime',
                        array('required' => false, 'widget' => 'single_text'))
                ->add('category', 'entity',
                        array(
                    'class' => 'PROCERGSLoginCidadaoCoreBundle:Notification\Category',
                    'property' => 'id'
                ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\Notification',
            'csrf_protection' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return '';
    }

}
