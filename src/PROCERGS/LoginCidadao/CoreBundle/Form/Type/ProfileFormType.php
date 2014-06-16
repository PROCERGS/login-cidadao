<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Uf;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManager;

class ProfileFormType extends BaseType
{
    private $em;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //parent::buildForm($builder, $options);
        $country = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Country')->findOneBy(array('iso' => 'BR'));
        $builder/* ->add('username', null,
                        array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle')) */
                ->add('email', 'email',
                        array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
                ->add('firstName', 'text',
                        array('label' => 'form.firstName', 'translation_domain' => 'FOSUserBundle'))
                ->add('surname', 'text',
                        array('label' => 'form.surname', 'translation_domain' => 'FOSUserBundle'))
                ->add('cep', null,
                        array('required' => false, 'label' => 'form.cep', 'translation_domain' => 'FOSUserBundle'))
                ->add('cpf', null,
                        array('required' => false, 'label' => 'form.cpf', 'translation_domain' => 'FOSUserBundle'))
                ->add('birthdate', 'birthday',
                        array(
                    'required' => false,
                    'format' => 'dd MMMM yyyy',
                    'widget' => 'choice',
                    'years' => range(date('Y'), 1898),
                    'label' => 'form.birthdate',
                    'translation_domain' => 'FOSUserBundle')
                )
                ->add('mobile', null,
                        array('required' => false, 'label' => 'form.mobile', 'translation_domain' => 'FOSUserBundle'))
                ->add('image')
                ->add('voterRegistration', 'text', array('required' => false, 'label' => 'form.voterRegistration', 'translation_domain' => 'FOSUserBundle'))
                ->add('country', 'entity',array(
                    'required' => false,
                    'class' => 'PROCERGSLoginCidadaoCoreBundle:Country',
                    'property' => 'name',
                    'preferred_choices' => array($country),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->where('u.reviewed = ' . Country::REVIEWED_OK)
                            ->orderBy('u.name', 'ASC');
                    }
                ))
                ->add('adress', 'text', array('required' => false))
                ->add('adressnumber', 'integer', array('required' => false))
                ;
                $builder->add('ufsteppe', 'text', array("required"=> false, "mapped"=>false));
                $builder->add('citysteppe', 'text', array("required"=> false, "mapped"=>false));
                $builder->add('ufpreferred', 'hidden', array("data" => $country->getId(),"required"=> false, "mapped"=>false));
                
                $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                    $person = $event->getData();
                    $form = $event->getForm();
                    $form->add('uf', 'entity',array(
                        'required' => false,
                        'class' => 'PROCERGSLoginCidadaoCoreBundle:Uf',
                        'property' => 'name',
                        'empty_value' => '',
                        'query_builder' => function(EntityRepository $er) use ($person) {
                            if ($person) {
                                $pars['country'] = $person->getCountry();
                                $pars['u'] = $person->getUf();
                            } else {
                                $pars['country'] = null;
                                $pars['u'] = null;
                            }
                            return $er->createQueryBuilder('u')
                            ->where('u.reviewed = ' . Uf::REVIEWED_OK)
                            ->andWhere('u.country = :country')
                            ->orWhere('u = :u')
                            ->setParameters($pars)
                            ->orderBy('u.name', 'ASC');
                        }
                    ));
                    $form->add('city', 'entity',array(
                        'required' => false,
                        'class' => 'PROCERGSLoginCidadaoCoreBundle:City',
                        'property' => 'name',
                        'empty_value' => '',
                        'query_builder' => function(EntityRepository $er) use ($person) {
                            if ($person) {
                                $pars['u'] = $person->getCity();
                                $pars['uf'] = $person->getUf();
                            } else {
                                $pars['u'] = null;
                                $pars['uf'] = null;
                            }
                            return $er->createQueryBuilder('u')
                            ->where('u.reviewed = ' . City::REVIEWED_OK)
                            ->andWhere('u.uf = :uf')
                            ->orWhere('u = :u')
                            ->setParameters($pars)
                            ->orderBy('u.name', 'ASC');
                        }
                    ));
                    
                });
                

    }

    public function getName()
    {
        return 'procergs_person_profile';
    }
    
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }

}
