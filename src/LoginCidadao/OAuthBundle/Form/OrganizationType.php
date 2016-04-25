<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use LoginCidadao\OAuthBundle\Model\OrganizationInterface;

class OrganizationType extends AbstractType
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $members = function (EntityRepository $er) {
            return $er->createQueryBuilder('p')
                ->innerJoin(
                    'LoginCidadaoOAuthBundle:Organization',
                    'o',
                    'WITH',
                    'p MEMBER OF o.members'
                );
        };

        $builder
            ->add(
                'name',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('label' => 'organizations.form.name.label')
            )
            ->add(
                'domain',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('label' => 'organizations.form.domain.label')
            )
            ->add(
                'sectorIdentifierUri',
                'Symfony\Component\Form\Extension\Core\Type\UrlType',
                array('label' => 'organizations.form.sectorIdentifierUri.label')
            );

        if ($this->authorizationChecker->isGranted('ROLE_ORGANIZATIONS_CAN_TRUST')) {
            $builder->add(
                'trusted',
                'LoginCidadao\CoreBundle\Form\Type\SwitchType',
                array('label' => 'organizations.form.trusted.label', 'required' => false)
            );
        }
        if ($this->authorizationChecker->isGranted('ROLE_ORGANIZATIONS_VALIDATE')
            && $builder->getData()->getId()
        ) {
            $builder->add(
                'validationUrl',
                'Symfony\Component\Form\Extension\Core\Type\UrlType',
                array(
                    'required' => false,
                    'label' => 'organizations.form.validationUrl.label',
                )
            );
        }

        $organization = $builder->getData();
        $this->prepareMembersField($builder, $organization);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'LoginCidadao\OAuthBundle\Entity\Organization',
            )
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    private function prepareMembersField(
        FormBuilderInterface $builder,
        OrganizationInterface $organization
    ) {
        $checker = $this->authorizationChecker;
        $person = $this->tokenStorage->getToken()->getUser();

        if (!$checker->isGranted('ROLE_ORGANIZATIONS_MANAGE_MEMBERS_ANY_ORG') &&
            !$checker->isGranted('ROLE_ORGANIZATIONS_MANAGE_MEMBERS')
        ) {
            return;
        }

        if (!$organization->getMembers()->contains($person) &&
            !$checker->isGranted('ROLE_ORGANIZATIONS_MANAGE_MEMBERS_ANY_ORG')
        ) {
            return;
        }

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $entity = $event->getData();
                $form = $event->getForm();

                $qb = function (EntityRepository $er) use ($entity) {
                    $sql = $er->createQueryBuilder('u');
                    if (!empty($entity['members'])) {
                        $sql->where('u.id in (:members)');
                        $sql->setParameter('members', $entity['members']);
                        $sql->orderBy('u.username', 'ASC');
                    } else {
                        $sql->where('1 != 1');
                    }

                    return $sql;
                };

                $form->add(
                    'members',
                    'LoginCidadao\CoreBundle\Form\Type\AjaxChoiceType',
                    array(
                        'label' => 'organizations.form.members.label',
                        'ajax_choice_attr' => array(
                            'filter' => array(
                                'route' => 'lc_organizations_members_filter',
                                'search_prop' => 'username',
                                'extra_form_prop' => array('service_id' => 'id'),
                            ),
                            'selected' => array(
                                'route' => 'lc_organizations_members',
                                'extra_form_prop' => array('person_id' => 'members'),
                            ),
                            'property_value' => 'id',
                            'property_text' => 'fullNameOrUsername',
                            'search_prop_label' => 'organizations.form.members.search.label',
                        ),
                        'required' => false,
                        'class' => 'LoginCidadaoCoreBundle:Person',
                        'choice_label' => 'fullNameOrUsername',
                        'query_builder' => $qb,
                    )
                );
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $entity = $event->getData();
                $form = $event->getForm();

                $qb = function (EntityRepository $er) use (&$entity) {
                    return $er->createQueryBuilder('p')
                        ->innerJoin(
                            'LoginCidadaoOAuthBundle:Organization',
                            'o',
                            'WITH',
                            'p MEMBER OF o.members'
                        )
                        ->orderBy('p.username', 'ASC');
                };

                if ($entity->getId()) {
                    $form->add(
                        'members',
                        'LoginCidadao\CoreBundle\Form\Type\AjaxChoiceType',
                        array(
                            'label' => 'organizations.form.members.label',
                            'ajax_choice_attr' => array(
                                'filter' => array(
                                    'route' => 'lc_organizations_members_filter',
                                    'search_prop' => 'username',
                                    'extra_form_prop' => array('service_id' => 'id'),
                                ),
                                'selected' => array(
                                    'route' => 'lc_organizations_members',
                                    'extra_form_prop' => array('person_id' => 'members'),
                                ),
                                'property_value' => 'id',
                                'property_text' => 'fullNameOrUsername',
                                'search_prop_label' => 'organizations.form.members.search.label',
                            ),
                            'required' => false,
                            'class' => 'LoginCidadaoCoreBundle:Person',
                            'choice_label' => 'fullNameOrUsername',
                            'query_builder' => $qb,
                        )
                    );
                }
            }
        );
    }
}
