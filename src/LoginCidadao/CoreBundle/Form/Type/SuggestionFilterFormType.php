<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuggestionFilterFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', TextType::class, ['required' => false, 'label' => 'sugg.username']);
        $builder->add('dateini', DateType::class, [
            'required' => false,
            'format' => 'dd/MM/yyyy',
            'widget' => 'single_text',
            'years' => range(date('Y'), 1898),
            'label' => 'sugg.dateini',
            'attr' => ['pattern' => '[0-9/]*', 'class' => 'date'],
        ]);
        $builder->add('dateend', DateType::class, [
            'required' => false,
            'format' => 'dd/MM/yyyy',
            'widget' => 'single_text',
            'years' => range(date('Y'), 1898),
            'label' => 'sugg.dateend',
            'attr' => ['pattern' => '[0-9/]*', 'class' => 'date'],
        ]);
        $builder->add('text', TextType::class, ['required' => false, 'label' => 'sugg.text']);
        $builder->setMethod('GET');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }
}
