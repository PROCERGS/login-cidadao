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
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;

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
                ->add('birthdate', 'birthday', array(
                    'required' => false,
                    'format' => 'dd/MM/yyyy',
                    'widget' => 'single_text',
                    'label' => 'form.birthdate',
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => array('pattern' => '[0-9/]*', 'class' => 'birthdate')
                    )
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
                            ->where('u.reviewed = :reviewed')
                            ->setParameter('reviewed', Country::REVIEWED_OK)
                            ->orderBy('u.name', 'ASC');
                    },
                    'label' => 'Place of birth - Country'
                ))
                ;
                $builder->add('nationality', 'entity',array(
                    'required' => false,
                    'class' => 'PROCERGSLoginCidadaoCoreBundle:Country',
                    'property' => 'name',
                    'preferred_choices' => array($country),
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                        ->where('u.reviewed = :reviewed')
                        ->setParameter('reviewed', Country::REVIEWED_OK)
                        ->orderBy('u.name', 'ASC');
                    },
                    'label' => 'Nationality'
                        ));                
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
                            if ($person instanceof PersonInterface) {
                                $country = $person->getCountry();
                                $state = $person->getState();
                            } else {
                                $country = null;
                                $state = null;
                            }

                            $params = array(
                                'reviewed' => State::REVIEWED_OK,
                                'country' => $country instanceof Country ? $country : null,
                                'state' => $state instanceof State ? $state : null
                            );

                            return $er->createQueryBuilder('s')
                                ->where('s.reviewed = :reviewed')
                                ->andWhere('s.country = :country')
                                ->orWhere('s = :state')
                                ->setParameters($params)
                                ->orderBy('s.name', 'ASC');
                        },
                        'label' => 'Place of birth - State'
                    ));
                    $form->add('city', 'entity',array(
                        'required' => false,
                        'class' => 'PROCERGSLoginCidadaoCoreBundle:City',
                        'property' => 'name',
                        'empty_value' => '',
                        'query_builder' => function(EntityRepository $er) use ($person) {
                            if ($person instanceof PersonInterface) {
                                $country = $person->getCountry();
                                $state = $person->getState();
                                $city = $person->getCity();
                            } else {
                                $country = null;
                                $state = null;
                                $city = null;
                            }
                            $params = array(
                                'reviewed' => State::REVIEWED_OK,
                                'state' => ($country instanceof Country && $state instanceof State) ? $state : null,
                                'city' => ($country instanceof Country && $state instanceof State && $city instanceof City) ? $city : null
                            );
                            return $er->createQueryBuilder('c')
                                ->where('c.reviewed = :reviewed')
                                ->andWhere('c.state = :state')
                                ->orWhere('c = :city')
                                ->setParameters($params)
                                ->orderBy('c.name', 'ASC');
                        },
                        'label' => 'Place of birth - City'
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
                            $params = array(
                                'reviewed' => State::REVIEWED_OK,
                                'country' => isset($data['country']) ? $data['country'] : null,
                                'stateId' => isset($data['state']) ? $data['state'] : null
                            );
                            return $er->createQueryBuilder('s')
                                    ->where('s.reviewed = :reviewed')
                                    ->andWhere('s.country = :country')
                                    ->orWhere('s.id = :stateId')
                                    ->setParameters($params)
                                    ->orderBy('s.name', 'ASC');
                        }
                    ));
                    $form->add('city', 'entity',array(
                        'required' => false,
                        'class' => 'PROCERGSLoginCidadaoCoreBundle:City',
                        'property' => 'name',
                        'empty_value' => '',
                        'query_builder' => function(EntityRepository $er) use ($data) {
                            $params = array(
                                'reviewed' => State::REVIEWED_OK,
                                'state' => isset($data['state']) ? $data['state'] : null,
                                'cityId' => isset($data['city']) ? $data['city'] : null
                            );
                            return $er->createQueryBuilder('c')
                                        ->where('c.reviewed = :reviewed')
                                        ->andWhere('c.state = :state')
                                        ->orWhere('c.id = :cityId')
                                        ->setParameters($params)
                                        ->orderBy('c.name', 'ASC');
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
