<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EndSessionForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $type = 'Symfony\Component\Form\Extension\Core\Type\CheckboxType';
        //$type = 'LoginCidadao\CoreBundle\Form\Type\SwitchType';
        if ($options['getLogoutConsent']) {
            $builder->add('logout', $type, ['label' => false, 'required' => false]);
        }
        if ($options['getRedirectConsent']) {
            $builder->add('redirect', $type, ['label' => false, 'required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'getLogoutConsent' => true,
                'getRedirectConsent' => false,
            ]
        );
    }
}
