<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Form;

use LoginCidadao\CoreBundle\Form\Type\SwitchType;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use LoginCidadao\CoreBundle\Form\DataTransformer\FromArray;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ClientMetadataWebForm extends AbstractType
{
    const RESPONSE_TYPE_CHOICES = [
        'code' => 'code',
        'id_token' => 'id_token',
        'token id_token' => 'token id_token',
        'code id_token' => 'code id_token',
        'code token' => 'code token',
        'code token id_token' => 'code token id_token',
    ];

    const GRANT_TYPE_CHOICES = [
        'authorization_code' => 'authorization_code',
        'implicit' => 'implicit',
        'refresh_token' => 'refresh_token',
        'client_credentials' => 'client_credentials',
    ];

    const SUBJECT_TYPE_CHOICES = [
        'pairwise' => 'pairwise',
        'public' => 'public',
    ];

    const TOKEN_ENDPOINT_AUTH_METHOD_CHOICES = [
        'client_secret_basic' => 'client_secret_basic',
        'client_secret_post' => 'client_secret_post',
        'client_secret_jwt' => 'client_secret_jwt',
        'private_key_jwt' => 'private_key_jwt',
        'none' => 'none',
    ];

    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /**
     * ClientMetadataWebForm constructor.
     * @param AuthorizationCheckerInterface $authChecker
     */
    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organization', TextType::class, ['disabled' => true])
            ->add(
                $builder->create('request_uris', TextareaType::class, ['required' => false])
                    ->addModelTransformer(new FromArray())
            )
            ->add(
                $builder->create('post_logout_redirect_uris', TextareaType::class, ['required' => false])
                    ->addModelTransformer(new FromArray())
            )
            ->add('response_types', ChoiceType::class, [
                'multiple' => true,
                'choices' => self::RESPONSE_TYPE_CHOICES,
            ])
            ->add('grant_types', ChoiceType::class, [
                'multiple' => true,
                'choices' => self::GRANT_TYPE_CHOICES,
            ])
            ->add('application_type', ChoiceType::class, ['choices' => ['web' => 'web', 'native' => 'native']])
            ->add('logo_uri', UrlType::class)
            ->add('policy_uri', UrlType::class, ['required' => false])
            ->add('jwks_uri', UrlType::class, ['required' => false])
            ->add('jwks', TextType::class, ['required' => false])
            ->add('sector_identifier_uri', UrlType::class, ['required' => false])
            ->add('id_token_signed_response_alg', TextType::class, ['required' => false])
            ->add('id_token_encrypted_response_alg', TextType::class, ['required' => false])
            ->add('id_token_encrypted_response_enc', TextType::class, ['required' => false])
            ->add('userinfo_signed_response_alg', TextType::class, ['required' => false])
            ->add('userinfo_encrypted_response_alg', TextType::class, ['required' => false])
            ->add('userinfo_encrypted_response_enc', TextType::class, ['required' => false])
            ->add('request_object_signing_alg', TextType::class, ['required' => false])
            ->add('request_object_encryption_alg', TextType::class, ['required' => false])
            ->add('request_object_encryption_enc', TextType::class, ['required' => false])
            ->add('token_endpoint_auth_method', ChoiceType::class,
                ['choices' => self::TOKEN_ENDPOINT_AUTH_METHOD_CHOICES,])
            ->add('token_endpoint_auth_signing_alg', TextType::class, ['required' => false])
            ->add('default_max_age', IntegerType::class, ['required' => false])
            ->add('require_auth_time', SwitchType::class, ['required' => false])
            ->add(
                $builder->create('default_acr_values', TextareaType::class, ['required' => false])
                    ->addModelTransformer(new FromArray())
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, $this->getCheckSubjectTypeCallback());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => ClientMetadata::class,
            'csrf_protection' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

    private function getCheckSubjectTypeCallback()
    {
        $authChecker = $this->authChecker;

        return function (FormEvent $event) use ($authChecker) {
            if ($authChecker->isGranted('ROLE_EDIT_CLIENT_SUBJECT_TYPE')) {
                $event->getForm()
                    ->add('subject_type', ChoiceType::class, ['choices' => self::SUBJECT_TYPE_CHOICES]);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oidc_client_metadata_form_type';
    }
}
