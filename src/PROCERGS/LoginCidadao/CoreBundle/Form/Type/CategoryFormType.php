<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\CommonFormType;

class CategoryFormType extends CommonFormType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $person = $this->getUser();
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use($person)
        {
            $cat = $event->getData();
            $form = $event->getForm();
            if ($cat->getId()) {
                $name = $cat->getClient()
                    ->getName();
                $form->add('client', 'text', array(
                    'required' => true,
                    'label' => 'Service',
                    'mapped' => false,
                    'read_only' => true,
                    'data' => $name,
                ));
                $form->add('mailTemplate', 'textarea', array(
                    'required' => true,
                    'attr' => array('rows' => 4)
                ));
                $form->add('markdownTemplate', 'textarea', array(
                    'required' => true,
                    'attr' => array('rows' => 4)
                ));
                $form->add('id', 'integer', array(
                    'required' => false,
                    'read_only' => true
                ));
            } else {
                $form->add('id', 'hidden', array(
                    'required' => false,
                    'read_only' => true
                ));                
                $form->add('client', 'entity', array(
                    'required' => true,
                    'label' => 'Service',
                    'class' => 'PROCERGSOAuthBundle:Client',
                    'property' => 'name',
                    'query_builder' => function (EntityRepository $er) use($person)
                    {
                        return $er->createQueryBuilder('u')
                        ->where('u.person = :person')
                        ->setParameter('person', $person)
                        ->orderBy('u.name', 'ASC');
                    }
                ));
                $form->add('defaultTitle', 'text', array(
                    'required' => true
                ));
                $form->add('defaultShortText', 'text', array(
                    'required' => true
                ));
            }
        });
        $builder->add('name', 'text', array(
            'required' => true
        ));
        $builder->add('defaultIcon', 'choice', array(
            'choices' => array(
                'glyphicon glyphicon-envelope' => 'envelope',
                'glyphicon glyphicon-exclamation-sign' => 'exclamation-sign'
            ),
            'required' => true
        ));
        $builder->add('mailSenderAddress', 'text', array(
            'required' => true
        ));
        $builder->add('emailable', 'choice', array(
            'choices' => array(
                '0' => 'no',
                '1' => 'yes'
            ),
            'required' => true
        ));
    }

    public function getName()
    {
        return 'category_form_type';
    }

}
