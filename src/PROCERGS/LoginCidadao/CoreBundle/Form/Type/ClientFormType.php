<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class ClientFormType extends ClientBaseFormType
{
    protected $lcScope;

    public function setLcScope($var)
    {
        $this->lcScope = $var;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'client_form_type';
    }
}
