<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;

class DocFormType extends AbstractType
{
    private $em;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cpf', 'text',
            array(
            'required' => false
        ));
    }

    public function getName()
    {
        return 'procergs_person_doc';
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }
}