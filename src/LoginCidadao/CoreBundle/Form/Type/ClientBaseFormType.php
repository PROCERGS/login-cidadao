<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Form\Type;

use LoginCidadao\OpenIDBundle\Form\ClientMetadataWebForm;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use LoginCidadao\CoreBundle\Form\DataTransformer\FromArray;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Valid;
use Vich\UploaderBundle\Form\Type\VichFileType;

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
        $builder
            ->add('name', TextType::class, ['required' => true])
            ->add('description', TextareaType::class, ['required' => true, 'attr' => array('rows' => 4)])
            ->add('metadata', ClientMetadataWebForm::class)
            ->add('siteUrl', TextType::class, ['required' => true])
            ->add(
                $builder
                    ->create('redirectUris', TextareaType::class, ['required' => true, 'attr' => ['rows' => 4]])
                    ->addModelTransformer(new FromArray())
            )
            ->add('landingPageUrl', TextType::class, ['required' => true])
            ->add('termsOfUseUrl', TextType::class, ['required' => true])
            ->add('image', VichFileType::class, [
                'required' => false,
                'allow_delete' => true, // not mandatory, default is true
                'download_uri' => true, // not mandatory, default is true
            ])
            ->add('id', HiddenType::class, ['required' => false])
            ->add('published', SwitchType::class, ['required' => false])
            ->add('visible', SwitchType::class, ['required' => false]);

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $entity = $event->getData();
                $form = $event->getForm();
                $form->add(
                    'owners',
                    AjaxChoiceType::class,
                    [
                        'label' => 'dev.ac.owners',
                        'ajax_choice_attr' => [
                            'filter' => [
                                'route' => 'lc_dev_client_grid_developer_filter',
                                'search_prop' => 'username',
                                'extra_form_prop' => ['service_id' => 'id'],
                            ],
                            'selected' => [
                                'route' => 'lc_dev_client_grid_developer',
                                'extra_form_prop' => ['person_id' => 'owners'],
                            ],
                            'property_value' => 'id',
                            'property_text' => 'fullNameOrUsername',
                            'search_prop_label' => 'dev.client.persons.filter',
                        ],
                        'required' => false,
                        'class' => 'LoginCidadaoCoreBundle:Person',
                        'choice_label' => 'fullNameOrUsername',
                        'query_builder' => function (EntityRepository $er) use (&$entity) {
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
                    ]
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
                        AjaxChoiceType::class,
                        [
                            'label' => 'dev.ac.owners',
                            'ajax_choice_attr' => [
                                'filter' => [
                                    'route' => 'lc_dev_client_grid_developer_filter',
                                    'search_prop' => 'username',
                                    'extra_form_prop' => ['service_id' => 'id'],
                                ],
                                'selected' => [
                                    'route' => 'lc_dev_client_grid_developer',
                                    'extra_form_prop' => ['person_id' => 'owners'],
                                ],
                                'property_value' => 'id',
                                'property_text' => 'fullNameOrUsername',
                                'search_prop_label' => 'dev.client.persons.filter',
                            ],
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
                        ]
                    );
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['constraints' => new Valid()]);
    }
}
