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

class ClientMetadataForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('client_id')
            ->add('client_secret')
            ->add('redirect_uris')
            ->add('post_logout_redirect_uris')
            ->add('response_types')
            ->add('grant_types')
            ->add('application_type')
            ->add('contacts')
            ->add('client_name')
            ->add('logo_uri', 'text')
            ->add('client_uri', 'text')
            ->add('policy_uri', 'text')
            ->add('tos_uri', 'text')
            ->add('jwks_uri')
            ->add('jwks')
            ->add('sector_identifier_uri', 'text')
            ->add('subject_type')
            ->add('id_token_signed_response_alg')
            ->add('id_token_encrypted_response_alg')
            ->add('id_token_encrypted_response_enc')
            ->add('userinfo_signed_response_alg')
            ->add('userinfo_encrypted_response_alg')
            ->add('userinfo_encrypted_response_enc')
            ->add('request_object_signing_alg')
            ->add('request_object_encryption_alg')
            ->add('request_object_encryption_enc')
            ->add('token_endpoint_auth_method')
            ->add('token_endpoint_auth_signing_alg')
            ->add('default_max_age')
            ->add('require_auth_time')
            ->add('default_acr_values')
            ->add('initiate_login_uri', 'text')
            ->add('request_uris');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'LoginCidadao\OpenIDBundle\Entity\ClientMetadata',
            'csrf_protection' => false
        ));
    }

    public function getName()
    {
        return '';
    }
}
