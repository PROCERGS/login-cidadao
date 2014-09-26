<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PROCERGS\LoginCidadao\CoreBundle\Form\DataTransformer\FromArray;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
class ClientBaseFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'required' => true
        ));
        $builder->add('description', 'textarea', array(
            'required' => true,
            'attr' => array('rows' => 4)
        ));
        $builder->add('siteUrl', 'text', array(
            'required' => true
        ));
        $builder->add($builder->create('redirectUris', 'textarea', array(
            'required' => true,
            'attr' => array('rows' => 4)
        ))->addModelTransformer(new FromArray()) );
        $builder->add('landingPageUrl', 'text', array(
            'required' => true
        ));
        $builder->add('termsOfUseUrl', 'text', array(
            'required' => true
        ));
                
        
        $builder->add('pictureFile');
        $builder->add('id', 'hidden', array(
            'required' => false
        ));
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entity = $event->getData();
            $form = $event->getForm();
            if ($entity->getId()) {
                $form->add('publicId', 'textarea', array(
                    'required' => false,
                    'read_only' => true,
                    'attr' => array('rows' => 4)
                ));
                $form->add('secret', 'textarea', array(
                    'required' => false,
                    'read_only' => true,
                    'attr' => array('rows' => 4)
                ));
            } else {
                $form->add('publicId', 'hidden', array(
                    'required' => false,
                ));
                $form->add('secret', 'hidden', array(
                    'required' => false,
                ));
            }
        });
    }

    public function getName()
    {
        return 'client_base_form_type';
    }
}
