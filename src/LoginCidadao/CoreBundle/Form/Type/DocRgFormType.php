<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Entity\Country;
use Symfony\Component\Form\AbstractType;

class DocRgFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('val', 'text', ['required' => true]);
        $builder->add('issuer', 'text', ['required' => true]);
        $builder->add('id', 'hidden', ['required' => false]);
        $builder->add('state', 'entity', [
            'required' => true,
            'class' => 'LoginCidadaoCoreBundle:State',
            'choice_label' => 'name',
            'query_builder' => function (EntityRepository $er) {
                $country = $er->createQueryBuilder('h')->getEntityManager()->getRepository('LoginCidadaoCoreBundle:Country')->findOneBy(array('iso2' => 'BR'));

                return $er->createQueryBuilder('u')
                    ->where('u.reviewed = '.Country::REVIEWED_OK)
                    ->andWhere('u.country = :country')
                    ->setParameter('country', $country)
                    ->orderBy('u.name', 'ASC');
            },
        ]);
    }

    public function getName()
    {
        return 'lc_person_doc';
    }
}
