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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use LoginCidadao\CoreBundle\Form\DataTransformer\FromArray;

class ClientMetadataWebForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organization', 'text', array('disabled' => true))
            ->add($builder->create('request_uris',
                'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                array('required' => false)
            )->addModelTransformer(new FromArray()))
            ->add($builder->create('post_logout_redirect_uris',
                'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                array('required' => false)
            )->addModelTransformer(new FromArray()))
            ->add('response_types',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                array(
                'multiple' => true,
                'choices' => array(
                    'code' => 'code',
                    'id_token' => 'id_token',
                    'token id_token' => 'token id_token',
                    'code id_token' => 'code id_token',
                    'code token' => 'code token',
                    'code token id_token' => 'code token id_token',
                )
            ))
            ->add('grant_types',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                array(
                'multiple' => true,
                'choices' => array(
                    'authorization_code' => 'authorization_code',
                    'implicit' => 'implicit',
                    'refresh_token' => 'refresh_token',
                    'client_credentials' => 'client_credentials'
                )
            ))
            ->add('application_type',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                array(
                'choices' => array('web' => 'web', 'native' => 'native')
            ))
            /*            ->add($builder->create('contacts', 'textarea',
              array('required' => false)
              )->addModelTransformer(new FromArray())) */
            ->add('logo_uri',
                'Symfony\Component\Form\Extension\Core\Type\UrlType')
            ->add('policy_uri',
                'Symfony\Component\Form\Extension\Core\Type\UrlType',
                array('required' => false))
            ->add('jwks_uri',
                'Symfony\Component\Form\Extension\Core\Type\UrlType',
                array('required' => false))
            ->add('jwks', 'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('sector_identifier_uri',
                'Symfony\Component\Form\Extension\Core\Type\UrlType',
                array('required' => false))
            ->add('subject_type',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                array(
                'choices' => array('public' => 'public', 'pairwise' => 'pairwise')
            ))
            ->add('id_token_signed_response_alg',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('id_token_encrypted_response_alg',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('id_token_encrypted_response_enc',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('userinfo_signed_response_alg',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('userinfo_encrypted_response_alg',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('userinfo_encrypted_response_enc',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('request_object_signing_alg',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('request_object_encryption_alg',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('request_object_encryption_enc',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('token_endpoint_auth_method',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                array(
                'choices' => array(
                    'client_secret_basic' => 'client_secret_basic',
                    'client_secret_post' => 'client_secret_post',
                    'client_secret_jwt' => 'client_secret_jwt',
                    'private_key_jwt' => 'private_key_jwt',
                    'none' => 'none'
                )
            ))
            ->add('token_endpoint_auth_signing_alg',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                array('required' => false))
            ->add('default_max_age',
                'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                array('required' => false))
            ->add('require_auth_time',
                'LoginCidadao\CoreBundle\Form\Type\SwitchType',
                array('required' => false))
            ->add($builder->create('default_acr_values',
                    'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                    array('required' => false)
                )->addModelTransformer(new FromArray()))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'LoginCidadao\OpenIDBundle\Entity\ClientMetadata',
            'csrf_protection' => true
        ));
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
