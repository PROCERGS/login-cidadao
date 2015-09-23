<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;
use PROCERGS\OAuthBundle\Entity\Client;

class ClientMetadata
{
    protected $client_id;
    protected $client_secret;

    /**
     * @Assert\All({
     *      @Assert\Type(type="string"),
     *      @Assert\NotBlank,
     *      @Assert\Url
     * })
     */
    protected $redirect_uris;

    /**
     * @Assert\All({
     *      @Assert\Type("string")
     * })
     */
    protected $response_types = array('code');

    /**
     * @Assert\All({
     *      @Assert\Type("string")
     * })
     */
    protected $grant_types = array('authorization_code');

    /** @Assert\Type(type="string") */
    protected $application_type = 'web';

    /** @Assert\Type(type="array") */
    protected $contacts;

    /** @Assert\Type(type="string") */
    protected $client_name;

    /**
     * @Assert\Type(type="string")
     * @Assert\Url
     */
    protected $logo_uri;

    /**
     * @Assert\Type(type="string")
     * @Assert\Url
     */
    protected $client_uri;

    /**
     * @Assert\Type(type="string")
     * @Assert\Url
     */
    protected $policy_uri;

    /**
     * @Assert\Url
     * @Assert\Type(type="string")
     */
    protected $tos_uri;

    /**
     * @Assert\Url
     * @Assert\Type(type="string")
     */
    protected $jwks_uri;

    /** @Assert\Type(type="string") */
    protected $jwks;

    /**
     * @Assert\Url
     * @Assert\Type(type="string")
     */
    protected $sector_identifier_uri;

    /** @Assert\Type(type="string") */
    protected $subject_type;

    /** @Assert\Type(type="string") */
    protected $id_token_signed_response_alg;

    /** @Assert\Type(type="string") */
    protected $id_token_encrypted_response_alg;

    /** @Assert\Type(type="string") */
    protected $id_token_encrypted_response_enc;

    /** @Assert\Type(type="string") */
    protected $userinfo_signed_response_alg;

    /** @Assert\Type(type="string") */
    protected $userinfo_encrypted_response_alg;

    /** @Assert\Type(type="string") */
    protected $userinfo_encrypted_response_enc;

    /** @Assert\Type(type="string") */
    protected $request_object_signing_alg;

    /** @Assert\Type(type="string") */
    protected $request_object_encryption_alg;

    /** @Assert\Type(type="string") */
    protected $request_object_encryption_enc;

    /** @Assert\Type(type="string") */
    protected $token_endpoint_auth_method;

    /** @Assert\Type(type="string") */
    protected $token_endpoint_auth_signing_alg;

    /** @Assert\Type(type="integer") */
    protected $default_max_age;

    /** @Assert\Type(type="boolean") */
    protected $require_auth_time = false;

    /** @Assert\Type(type="array") */
    protected $default_acr_values;

    /**
     * @Assert\Url
     * @Assert\Type(type="string")
     */
    protected $initiate_login_uri;

    /**
     * @Assert\All({
     *      @Assert\Type("string"),
     *      @Assert\Url
     * })
     */
    protected $request_uris;

    public function __construct()
    {
        $this->response_types    = array('code');
        $this->grant_types       = array('authorization_code');
        $this->application_type  = 'web';
        $this->require_auth_time = false;
    }

    public function getRedirectUris()
    {
        return $this->redirect_uris;
    }

    public function setRedirectUris($redirect_uris)
    {
        $this->redirect_uris = $redirect_uris;
        return $this;
    }

    public function getResponseTypes()
    {
        return $this->response_types;
    }

    public function setResponseTypes($response_types)
    {
        $this->response_types = $response_types;
        return $this;
    }

    public function getGrantTypes()
    {
        return $this->grant_types;
    }

    public function setGrantTypes($grant_types)
    {
        $this->grant_types = $grant_types;
        return $this;
    }

    public function getApplicationType()
    {
        return $this->application_type;
    }

    public function setApplicationType($application_type)
    {
        $this->application_type = $application_type;
        return $this;
    }

    public function getContacts()
    {
        return $this->contacts;
    }

    public function setContacts($contacts)
    {
        $this->contacts = $contacts;
        return $this;
    }

    public function getClientName()
    {
        return $this->client_name;
    }

    public function setClientName($client_name)
    {
        $this->client_name = $client_name;
        return $this;
    }

    public function getLogoUri()
    {
        return $this->logo_uri;
    }

    public function setLogoUri($logo_uri)
    {
        $this->logo_uri = $logo_uri;
        return $this;
    }

    public function getClientUri()
    {
        return $this->client_uri;
    }

    public function setClientUri($client_uri)
    {
        $this->client_uri = $client_uri;
        return $this;
    }

    public function getPolicyUri()
    {
        return $this->policy_uri;
    }

    public function setPolicyUri($policy_uri)
    {
        $this->policy_uri = $policy_uri;
        return $this;
    }

    public function getTosUri()
    {
        return $this->tos_uri;
    }

    public function setTosUri($tos_uri)
    {
        $this->tos_uri = $tos_uri;
        return $this;
    }

    public function getJwksUri()
    {
        return $this->jwks_uri;
    }

    public function setJwksUri($jwks_uri)
    {
        $this->jwks_uri = $jwks_uri;
        return $this;
    }

    public function getJwks()
    {
        return $this->jwks;
    }

    public function setJwks($jwks)
    {
        $this->jwks = $jwks;
        return $this;
    }

    public function getSectorIdentifierUri()
    {
        return $this->sector_identifier_uri;
    }

    public function setSectorIdentifierUri($sector_identifier_uri)
    {
        $this->sector_identifier_uri = $sector_identifier_uri;
        return $this;
    }

    public function getSubjectType()
    {
        return $this->subject_type;
    }

    public function setSubjectType($subject_type)
    {
        $this->subject_type = $subject_type;
        return $this;
    }

    public function getIdTokenSignedResponseAlg()
    {
        return $this->id_token_signed_response_alg;
    }

    public function setIdTokenSignedResponseAlg($id_token_signed_response_alg)
    {
        $this->id_token_signed_response_alg = $id_token_signed_response_alg;
        return $this;
    }

    public function getIdTokenEncryptedResponseAlg()
    {
        return $this->id_token_encrypted_response_alg;
    }

    public function setIdTokenEncryptedResponseAlg($id_token_encrypted_response_alg)
    {
        $this->id_token_encrypted_response_alg = $id_token_encrypted_response_alg;
        return $this;
    }

    public function getIdTokenEncryptedResponseEnc()
    {
        return $this->id_token_encrypted_response_enc;
    }

    public function setIdTokenEncryptedResponseEnc($id_token_encrypted_response_enc)
    {
        $this->id_token_encrypted_response_enc = $id_token_encrypted_response_enc;
        return $this;
    }

    public function getUserinfoSignedResponseAlg()
    {
        return $this->userinfo_signed_response_alg;
    }

    public function setUserinfoSignedResponseAlg($userinfo_signed_response_alg)
    {
        $this->userinfo_signed_response_alg = $userinfo_signed_response_alg;
        return $this;
    }

    public function getUserinfoEncryptedResponseAlg()
    {
        return $this->userinfo_encrypted_response_alg;
    }

    public function setUserinfoEncryptedResponseAlg($userinfo_encrypted_response_alg)
    {
        $this->userinfo_encrypted_response_alg = $userinfo_encrypted_response_alg;
        return $this;
    }

    public function getUserinfoEncryptedResponseEnc()
    {
        return $this->userinfo_encrypted_response_enc;
    }

    public function setUserinfoEncryptedResponseEnc($userinfo_encrypted_response_enc)
    {
        $this->userinfo_encrypted_response_enc = $userinfo_encrypted_response_enc;
        return $this;
    }

    public function getRequestObjectSigningAlg()
    {
        return $this->request_object_signing_alg;
    }

    public function setRequestObjectSigningAlg($request_object_signing_alg)
    {
        $this->request_object_signing_alg = $request_object_signing_alg;
        return $this;
    }

    public function getRequestObjectEncryptionAlg()
    {
        return $this->request_object_encryption_alg;
    }

    public function setRequestObjectEncryptionAlg($request_object_encryption_alg)
    {
        $this->request_object_encryption_alg = $request_object_encryption_alg;
        return $this;
    }

    public function getRequestObjectEncryptionEnc()
    {
        return $this->request_object_encryption_enc;
    }

    public function setRequestObjectEncryptionEnc($request_object_encryption_enc)
    {
        $this->request_object_encryption_enc = $request_object_encryption_enc;
        return $this;
    }

    public function getTokenEndpointAuthMethod()
    {
        return $this->token_endpoint_auth_method;
    }

    public function setTokenEndpointAuthMethod($token_endpoint_auth_method)
    {
        $this->token_endpoint_auth_method = $token_endpoint_auth_method;
        return $this;
    }

    public function getTokenEndpointAuthSigningAlg()
    {
        return $this->token_endpoint_auth_signing_alg;
    }

    public function setTokenEndpointAuthSigningAlg($token_endpoint_auth_signing_alg)
    {
        $this->token_endpoint_auth_signing_alg = $token_endpoint_auth_signing_alg;
        return $this;
    }

    public function getDefaultMaxAge()
    {
        return $this->default_max_age;
    }

    public function setDefaultMaxAge($default_max_age)
    {
        $this->default_max_age = $default_max_age;
        return $this;
    }

    public function getRequireAuthTime()
    {
        return $this->require_auth_time;
    }

    public function setRequireAuthTime($require_auth_time)
    {
        $this->require_auth_time = $require_auth_time;
        return $this;
    }

    public function getDefaultAcrValues()
    {
        return $this->default_acr_values;
    }

    public function setDefaultAcrValues($default_acr_values)
    {
        $this->default_acr_values = $default_acr_values;
        return $this;
    }

    public function getInitiateLoginUri()
    {
        return $this->initiate_login_uri;
    }

    public function setInitiateLoginUri($initiate_login_uri)
    {
        $this->initiate_login_uri = $initiate_login_uri;
        return $this;
    }

    public function getRequestUris()
    {
        return $this->request_uris;
    }

    public function setRequestUris($request_uris)
    {
        $this->request_uris = $request_uris;
        return $this;
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function getClientSecret()
    {
        return $this->client_secret;
    }

    public function setClientId($client_id)
    {
        $this->client_id = $client_id;
        return $this;
    }

    public function setClientSecret($client_secret)
    {
        $this->client_secret = $client_secret;
        return $this;
    }

    /**
     * @param Client $client
     * @return ClientMetadata
     */
    public function fromClient(Client $client)
    {
        $this->setGrantTypes($client->getAllowedGrantTypes())
            ->setClientUri($client->getSiteUrl())
            ->setTosUri($client->getTermsOfUseUrl())
            ->setClientName($client->getName())
            ->setRedirectUris($client->getRedirectUris());

        $this->setClientId($client->getPublicId())
            ->setClientSecret($client->getSecret());

        return $this;
    }

    /**
     * @return Client
     */
    public function toClient()
    {
        $name    = $this->getClientName();
        $hasName = $name !== null && strlen($name) > 0;

        $grantTypes   = $this->getGrantTypes();
        $clientUri    = $this->getClientUri();
        $tosUri       = $this->getTosUri();
        $clientName   = $this->getClientName();
        $redirectUris = $this->getRedirectUris();

        $client = new Client();

        if ($grantTypes) {
            $client->setAllowedGrantTypes($grantTypes);
        }

        if ($clientUri) {
            $client->setLandingPageUrl($clientUri)
                ->setSiteUrl($clientUri);
        }

        if ($tosUri) {
            $client->setTermsOfUseUrl($tosUri);
        }

        if ($clientName) {
            $client->setName($clientName);
        }

        if ($redirectUris) {
            $client->setRedirectUris($redirectUris);
        }

        $client->setVisible($hasName)
            ->setPublished(false);

        return $client;
    }
}
