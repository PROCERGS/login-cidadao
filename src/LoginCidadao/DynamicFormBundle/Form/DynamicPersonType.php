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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints;

class DynamicPersonType extends AbstractType
{
    /**
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'LoginCidadao\CoreBundle\Entity\Person',
                'validation_groups' => ['Dynamic'],
                'constraints' => new Constraints\Valid(),
            ]
        );
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'person';
    }
}
