<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use LoginCidadao\CoreBundle\Form\Type\CommonFormType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Form\FormBuilderInterface;

class AjaxChoiceType extends CommonFormType
{

    public function getParent()
    {
        return 'Symfony\Bridge\Doctrine\Form\Type\EntityType';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'transformer' => null,
            'ajax_choice_attr' => array(),
            'attr' => array(
                'style' => 'display:none;'
            ),
            'multiple' => true
        ));
    }

    /**
     *
     * @param FormView $view            
     * @param FormInterface $form            
     * @param array $options            
     */
    public function buildView(FormView $view, FormInterface $form,
                                array $options)
    {
        if (array_key_exists('ajax_choice_attr', $options)) {
            $nForm                                    = $form->getParent()->getName();
            $options['ajax_choice_attr']['holder_id'] = $nForm.'_'.$form->getName();
            if (isset($options['ajax_choice_attr']['filter'])) {
                $this->transformation1($options['ajax_choice_attr']['filter'],
                    $nForm);
            }
            if (isset($options['ajax_choice_attr']['selected'])) {
                $this->transformation1($options['ajax_choice_attr']['selected'],
                    $nForm);
            }
            if (isset($options['ajax_choice_attr']['search_prop_label'])) {
                $view->vars['ajax_choice_search_prop_label'] = $this->translator->trans($options['ajax_choice_attr']['search_prop_label']);
                unset($options['ajax_choice_attr']['search_prop_label']);
            }
            $view->vars['ajax_choice_attr'] = & $options['ajax_choice_attr'];
        }
    }

    private function transformation1(&$grid, &$nForm)
    {
        if (isset($grid['route'])) {
            $grid['route'] = $this->generateUrl($grid['route']);
        }
        if (isset($grid['extra_form_prop'])) {
            foreach ($grid['extra_form_prop'] as &$extraForm) {
                $extraForm = $nForm.'_'.$extraForm;
            }
        }
    }
}
