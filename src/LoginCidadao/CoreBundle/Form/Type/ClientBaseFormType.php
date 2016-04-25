<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use LoginCidadao\CoreBundle\Form\DataTransformer\FromArray;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use LoginCidadao\OpenIDBundle\Form\ClientMetadataWebForm;

class ClientBaseFormType extends AbstractType
{
    /** @var AuthorizationCheckerInterface */
    protected $security;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $checker = $this->security;
        $person = $this->tokenStorage->getToken()->getUser();

        $organizationQueryBuilder = function (EntityRepository $er) use ($person, $checker) {
            $query = $er->createQueryBuilder('o');
            if (!$checker->isGranted('ROLE_ORGANIZATIONS_BIND_CLIENT_ANY_ORG')) {
                $query->where(':person MEMBER OF o.members')
                    ->setParameters(compact('person'));
            }

            return $query;
        };

        $builder->add(
            'name',
            'Symfony\Component\Form\Extension\Core\Type\TextType',
            array('required' => true)
        )
            ->add(
                'description',
                'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                array('required' => true, 'attr' => array('rows' => 4))
            )
            ->add(
                'metadata',
                'LoginCidadao\OpenIDBundle\Form\ClientMetadataWebForm'
            )
            ->add(
                'siteUrl',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => true)
            )
            ->add(
                $builder->create(
                    'redirectUris',
                    'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                    array(
                        'required' => true,
                        'attr' => array('rows' => 4),
                    )
                )
                    ->addModelTransformer(new FromArray())
            )
            ->add(
                'landingPageUrl',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => true)
            )
            ->add(
                'termsOfUseUrl',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => true)
            )
            ->add(
                'image',
                'Vich\UploaderBundle\Form\Type\VichFileType',
                array(
                    'required' => false,
                    'allow_delete' => true, // not mandatory, default is true
                    'download_link' => true, // not mandatory, default is true
                )
            )
            ->add(
                'id',
                'Symfony\Component\Form\Extension\Core\Type\HiddenType',
                array(
                    'required' => false,
                )
            )
            ->add(
                'published',
                'LoginCidadao\CoreBundle\Form\Type\SwitchType',
                array('required' => false)
            )
            ->add(
                'visible',
                'LoginCidadao\CoreBundle\Form\Type\SwitchType',
                array('required' => false)
            );

//        if ($checker->isGranted('ROLE_ORGANIZATIONS_BIND_CLIENT')) {
//            $builder->add('organization',
//                'Symfony\Bridge\Doctrine\Form\Type\EntityType',
//                array(
//                'required' => false,
//                'placeholder' => 'organizations.form.client_form.placeholder',
//                'class' => 'LoginCidadaoOAuthBundle:Organization',
//                'query_builder' => $organizationQueryBuilder
//            ));
//        }

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $entity = $event->getData();
                $form = $event->getForm();
                $form->add(
                    'owners',
                    'LoginCidadao\CoreBundle\Form\Type\AjaxChoiceType',
                    array(
                        'label' => 'dev.ac.owners',
                        'ajax_choice_attr' => array(
                            'filter' => array(
                                'route' => 'lc_dev_client_grid_developer_filter',
                                'search_prop' => 'username',
                                'extra_form_prop' => array('service_id' => 'id'),
                            ),
                            'selected' => array(
                                'route' => 'lc_dev_client_grid_developer',
                                'extra_form_prop' => array('person_id' => 'owners'),
                            ),
                            'property_value' => 'id',
                            'property_text' => 'fullNameOrUsername',
                            'search_prop_label' => 'dev.client.persons.filter',
                        ),
                        'required' => false,
                        'class' => 'LoginCidadaoCoreBundle:Person',
                        'choice_label' => 'fullNameOrUsername',
                        'query_builder' => function (EntityRepository $er) use (&$entity) {
                            //$country = $er->createQueryBuilder('h')->getEntityManager()->getRepository('LoginCidadaoCoreBundle:Person')->findOneBy(array('iso2' => 'BR'));
                            $sql = $er->createQueryBuilder('u');
                            if (!empty($entity['owners'])) {
                                $sql->where('u.id in (:owners)');
                                $sql->setParameter('owners', $entity['owners']);
                                $sql->orderBy('u.username', 'ASC');
                            } else {
                                $sql->where('1 != 1');
                            }

                            return $sql;
                        },
                    )
                );
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $entity = $event->getData();
                $form = $event->getForm();
                if ($entity->getId()) {
                    $form->add(
                        'owners',
                        'LoginCidadao\CoreBundle\Form\Type\AjaxChoiceType',
                        array(
                            'label' => 'dev.ac.owners',
                            'ajax_choice_attr' => array(
                                'filter' => array(
                                    'route' => 'lc_dev_client_grid_developer_filter',
                                    'search_prop' => 'username',
                                    'extra_form_prop' => array('service_id' => 'id'),
                                ),
                                'selected' => array(
                                    'route' => 'lc_dev_client_grid_developer',
                                    'extra_form_prop' => array('person_id' => 'owners'),
                                ),
                                'property_value' => 'id',
                                'property_text' => 'fullNameOrUsername',
                                'search_prop_label' => 'dev.client.persons.filter',
                            ),
                            'required' => false,
                            'class' => 'LoginCidadaoCoreBundle:Person',
                            'choice_label' => 'fullNameOrUsername',
                            'query_builder' => function (EntityRepository $er) use (&$entity) {
                                return $er->createQueryBuilder('u')
                                    ->where(':client MEMBER OF u.clients')->setParameter(
                                        'client',
                                        $entity
                                    )
                                    ->orderBy('u.username', 'ASC');
                            },
                        )
                    );
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
            )
        );
    }

}
