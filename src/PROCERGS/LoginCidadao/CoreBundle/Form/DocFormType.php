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

class DocFormType extends BaseForm
{
    /** @var MeuRSHelper */
    protected $meuRSHelper;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $voterRegistration = array($this, 'voterRegistrationCallback');
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $voterRegistration);
    }

    public function voterRegistrationCallback(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $data->personMeuRS = $this->meuRSHelper
            ->getPersonMeuRS($event->getData(), true);
        $event->setData($data);

        $form->add('personMeuRS', 'person_meurs_voter_registration_type',
            array(
            'compound' => true
        ));
        $form->get('personMeuRS')->add('voterRegistration', 'text',
            array(
            'required' => false
        ));
    }

    public function setMeuRSHelper(MeuRSHelper $meuRSHelper)
    {
        $this->meuRSHelper = $meuRSHelper;

        return $this;
    }
}
