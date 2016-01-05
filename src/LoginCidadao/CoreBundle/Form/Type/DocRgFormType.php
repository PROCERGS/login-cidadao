<?php
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

class DocRgFormType extends AbstractType
{

    private $em;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('val', 'text', array(
            'required' => true
        ));
        $builder->add('issuer', 'text', array(
            'required' => true
        ));
        $builder->add('id', 'hidden', array(
            'required' => false
        ));
        $builder->add('state', 'entity',array(
            'required' => true,
            'class' => 'LoginCidadaoCoreBundle:State',
            'choice_label' => 'name',
            'query_builder' => function(EntityRepository $er) {
                $country = $er->createQueryBuilder('h')->getEntityManager()->getRepository('LoginCidadaoCoreBundle:Country')->findOneBy(array('iso2' => 'BR'));
                return $er->createQueryBuilder('u')
                ->where('u.reviewed = ' . Country::REVIEWED_OK)
                ->andWhere('u.country = :country')
                ->setParameter('country', $country)
                ->orderBy('u.name', 'ASC');
            }
        ));
    }

    public function getName()
    {
        return 'lc_person_doc';
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }
}
