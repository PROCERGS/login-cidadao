<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManager;

class ProfileFormType extends BaseType
{
    protected $em;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //parent::buildForm($builder, $options);
        $country = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Country')->findOneBy(array('iso2' => 'BR'));
        $builder/* ->add('username', null,
                        array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle')) */
                ->add('email', 'email',
                        array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
                ->add('firstName', 'text',
                        array('label' => 'form.firstName', 'translation_domain' => 'FOSUserBundle'))
                ->add('surname', 'text',
                        array('label' => 'form.surname', 'translation_domain' => 'FOSUserBundle'))
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
                ;
                $builder->add('ufsteppe', 'text', array("required"=> false, "mapped"=>false));
                $builder->add('citysteppe', 'text', array("required"=> false, "mapped"=>false));
                $builder->add('ufpreferred', 'hidden', array("data" => $country->getId(),"required"=> false, "mapped"=>false));

                $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                    $person = $event->getData();
                    $form = $event->getForm();
                    $form->add('state', 'entity',array(
                        'required' => false,
                        'class' => 'PROCERGSLoginCidadaoCoreBundle:State',
                        'property' => 'name',
                        'empty_value' => '',
                        'query_builder' => function(EntityRepository $er) use ($person) {
                            if ($person) {
                                $pars['country'] = $person->getCountry();
                                $pars['u'] = $person->getState();
                            } else {
                                $pars['country'] = null;
                                $pars['u'] = null;
                            }
                            return $er->createQueryBuilder('u')
                            ->where('u.reviewed = ' . State::REVIEWED_OK)
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
                                $pars['state'] = $person->getState();
                            } else {
                                $pars['u'] = null;
                                $pars['state'] = null;
                            }
                            return $er->createQueryBuilder('u')
                            ->where('u.reviewed = ' . City::REVIEWED_OK)
                            ->andWhere('u.state = :state')
                            ->orWhere('u = :u')
                            ->setParameters($pars)
                            ->orderBy('u.name', 'ASC');
                        }
                    ));

                });
                $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                    $data = $event->getData();
                    $form = $event->getForm();
                    $form->add('state', 'entity',array(
                        'required' => false,
                        'class' => 'PROCERGSLoginCidadaoCoreBundle:State',
                        'property' => 'name',
                        'empty_value' => '',
                        'query_builder' => function(EntityRepository $er) use ($data) {
                            $pars = array();
                            $pars['country'] = isset($data['country'])? $data['country'] : null;
                            $pars['u'] = isset($data['state'])? $data['state'] : null;
                            return $er->createQueryBuilder('u')
                            ->where('u.reviewed = ' . State::REVIEWED_OK)
                            ->andWhere('u.country = :country')
                            ->orWhere('u.id = :u')
                            ->setParameters($pars)
                            ->orderBy('u.name', 'ASC');
                        }
                    ));
                    $form->add('city', 'entity',array(
                        'required' => false,
                        'class' => 'PROCERGSLoginCidadaoCoreBundle:City',
                        'property' => 'name',
                        'empty_value' => '',
                        'query_builder' => function(EntityRepository $er) use ($data) {
                            $pars = array();
                            $pars['state'] = isset($data['state']) ? $data['state'] : null;
                            $pars['u'] = isset($data['city']) ? $data['city'] : null;
                            return $er->createQueryBuilder('u')
                            ->where('u.reviewed = ' . City::REVIEWED_OK)
                            ->andWhere('u.state = :state')
                            ->orWhere('u.id = :u')
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
