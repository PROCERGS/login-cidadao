<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle\Form;

use LoginCidadao\DynamicFormBundle\Event\DynamicFormSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class DynamicFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formService = $options['dynamic_form_service'];
        $builder->addEventSubscriber(new DynamicFormSubscriber($formService));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('dynamic_form_service');
        $resolver->setDefaults(
            [
                'data_class' => 'LoginCidadao\DynamicFormBundle\Model\DynamicFormData',
                'validation_groups' => ['Dynamic'],
                'constraints' => new Constraints\Valid(),
            ]
        );
    }
}
