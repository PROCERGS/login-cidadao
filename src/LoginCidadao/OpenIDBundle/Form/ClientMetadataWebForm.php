<?php
/*
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
use PROCERGS\LoginCidadao\CoreBundle\Form\DataTransformer\FromArray;

class ClientMetadataWebForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder->create('request_uris', 'textarea',
                    array('required' => false)
                )->addModelTransformer(new FromArray()))
            ->add('response_types', 'choice',
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
            ->add('grant_types', 'choice',
                array(
                'multiple' => true,
                'choices' => array(
                    'authorization_code' => 'authorization_code',
                    'implicit' => 'implicit',
                    'refresh_token' => 'refresh_token'
                )
            ))
            ->add('application_type', 'choice',
                array(
                'choices' => array('web' => 'web', 'native' => 'native')
            ))
/*            ->add($builder->create('contacts', 'textarea',
                    array('required' => false)
                )->addModelTransformer(new FromArray()))*/
            ->add('logo_uri', 'url')
            ->add('policy_uri', 'url', array('required' => false))
            ->add('jwks_uri', 'url', array('required' => false))
            ->add('jwks', 'text', array('required' => false))
            ->add('sector_identifier_uri', 'url', array('required' => false))
            ->add('subject_type', 'choice',
                array(
                'choices' => array('public' => 'public', 'pairwise' => 'pairwise')
            ))
            ->add('id_token_signed_response_alg', 'text',
                array('required' => false))
            ->add('id_token_encrypted_response_alg', 'text',
                array('required' => false))
            ->add('id_token_encrypted_response_enc', 'text',
                array('required' => false))
            ->add('userinfo_signed_response_alg', 'text',
                array('required' => false))
            ->add('userinfo_encrypted_response_alg', 'text',
                array('required' => false))
            ->add('userinfo_encrypted_response_enc', 'text',
                array('required' => false))
            ->add('request_object_signing_alg', 'text',
                array('required' => false))
            ->add('request_object_encryption_alg', 'text',
                array('required' => false))
            ->add('request_object_encryption_enc', 'text',
                array('required' => false))
            ->add('token_endpoint_auth_method', 'choice',
                array(
                'choices' => array(
                    'client_secret_basic' => 'client_secret_basic',
                    'client_secret_post' => 'client_secret_post',
                    'client_secret_jwt' => 'client_secret_jwt',
                    'private_key_jwt' => 'private_key_jwt',
                    'none' => 'none'
                )
            ))
            ->add('token_endpoint_auth_signing_alg', 'text',
                array('required' => false))
            ->add('default_max_age', 'integer', array('required' => false))
            ->add('require_auth_time', 'switch', array('required' => false))
            ->add($builder->create('default_acr_values', 'textarea',
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

    public function getName()
    {
        return '';
    }
}
