<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Form;

use LoginCidadao\CoreBundle\Form\Type\DocFormType as BaseForm;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocFormType extends BaseForm
{
    /** @var MeuRSHelper */
    protected $meuRSHelper;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $voterRegistration = array($this, 'voterRegistrationCallback');
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $voterRegistration);
        $builder->add(
            'personMeuRS',
            'PROCERGS\LoginCidadao\CoreBundle\Form\PersonMeuRSVoterRegistrationType',
            ['compound' => true, 'validation_groups' => ['Documents'], 'cascade_validation' => true]
        );
    }

    public function voterRegistrationCallback(FormEvent $event)
    {
        $data = $event->getData();

        $data->personMeuRS = $this->meuRSHelper
            ->getPersonMeuRS($event->getData(), true);

        return;
    }

    public function setMeuRSHelper(MeuRSHelper $meuRSHelper)
    {
        $this->meuRSHelper = $meuRSHelper;

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'LoginCidadao\CoreBundle\Entity\Person',
                'validation_groups' => ['Documents'],
                'cascade_validation' => true,
            ]
        );
    }
}
