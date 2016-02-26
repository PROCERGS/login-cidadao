<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use LoginCidadao\CoreBundle\Form\Type\CommonFormType;

class CategoryFormType extends CommonFormType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $person = $this->getUser();

        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function(FormEvent $event) use($person) {
            $cat  = $event->getData();
            $form = $event->getForm();
            if ($cat->getId()) {
                $name = $cat->getClient()
                    ->getName();
                $form->add('client',
                    'Symfony\Component\Form\Extension\Core\Type\TextType',
                    array(
                    'required' => true,
                    'label' => 'Service',
                    'mapped' => false,
                    'read_only' => true,
                    'data' => $name,
                ));
                $form->add('mailTemplate',
                    'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                    array(
                    'required' => true,
                    'attr' => array('rows' => 4)
                ));
                $form->add('markdownTemplate',
                    'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                    array(
                    'required' => true,
                    'attr' => array('rows' => 4)
                ));
                $form->add('id',
                    'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    array(
                    'required' => false,
                    'read_only' => true
                ));
            } else {
                $form->add('id',
                    'Symfony\Component\Form\Extension\Core\Type\HiddenType',
                    array(
                    'required' => false,
                    'read_only' => true
                ));
                $form->add('client',
                    'Symfony\Bridge\Doctrine\Form\Type\EntityType',
                    array(
                    'required' => true,
                    'label' => 'Service',
                    'class' => 'LoginCidadaoOAuthBundle:Client',
                    'choice_label' => 'name',
                    'query_builder' => function(EntityRepository $er) use($person) {
                        return $er->createQueryBuilder('c')
                                ->where(':person MEMBER OF c.owners')
                                ->setParameter('person', $person)
                                ->orderBy('c.name', 'ASC');
                    }
                ));
            }
        });
        $builder->add('name',
            'Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
            'required' => true
        ));
        $builder->add('defaultIcon',
            'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
            array(
            'choices' => array(
                'glyphicon glyphicon-envelope' => 'envelope',
                'glyphicon glyphicon-exclamation-sign' => 'exclamation-sign'
            ),
            'required' => true
        ));
        $builder->add('defaultTitle',
            'Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
            'required' => true
        ));
        $builder->add('defaultShortText',
            'Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
            'required' => true
        ));
        $builder->add('mailSenderAddress',
            'Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
            'required' => true
        ));
        $builder->add('emailable',
            'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
            array(
            'choices' => array(
                '0' => 'No',
                '1' => 'Yes'
            ),
            'required' => true
        ));
    }
}
