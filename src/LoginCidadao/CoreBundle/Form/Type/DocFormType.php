<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Entity\Country;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Entity\City;
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
        return 'person_doc_form_type';
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }
}