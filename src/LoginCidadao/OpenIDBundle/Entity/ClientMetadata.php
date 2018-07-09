<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Entity;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OAuthBundle\Model\OrganizationInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use LoginCidadao\OpenIDBundle\Validator\Constraints\SectorIdentifierUri;
use Symfony\Component\Validator\Constraints as Assert;
use LoginCidadao\OAuthBundle\Entity\Client;
use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="LoginCidadao\OpenIDBundle\Entity\ClientMetadataRepository")
 * @UniqueEntity("client")
 * @UniqueEntity("client_name")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="client_metadata")
 * @JMS\ExclusionPolicy("all")
 * @SectorIdentifierUri
 */
class ClientMetadata
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    private $client_id;
    private $client_secret;

    /**
     * @var ClientInterface
     * @ORM\OneToOne(targetEntity="LoginCidadao\OAuthBundle\Entity\Client", inversedBy="metadata", cascade={"persist"})
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;

    /**
     * @var string[]
     *
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\All({
     *      @Assert\Type(type="string"),
     *      @Assert\NotBlank,
     *      @Assert\Url(checkDNS = false)
     * })
     * @ORM\Column(name="redirect_uris", type="json_array", nullable=false)
     */
    private $redirect_uris;

    /**
     * @var array
     *
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\All({
     *      @Assert\Type("string")
     * })
     * @ORM\Column(name="response_types", type="simple_array", nullable=false)
     */
    private $response_types = ['code'];

    /**
     * @var array
     *
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\All({
     *      @Assert\Type("string")
     * })
     * @ORM\Column(type="simple_array", nullable=false)
     */
    private $grant_types = ['authorization_code'];

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(name="application_type", type="string", length=100, nullable=false)
     */
    private $application_type = 'web';

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\All({
     *      @Assert\Type("string")
     * })
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $contacts;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", nullable=true)
     */
    private $client_name;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @Assert\Url(checkDNS = false)
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $logo_uri;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @Assert\Url(checkDNS = false)
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $client_uri;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @Assert\Url(checkDNS = false)
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $policy_uri;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Url(checkDNS = false)
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $tos_uri;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Url(checkDNS = false)
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $jwks_uri;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="text", nullable=true)
     */
    private $jwks;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Url(checkDNS = false, protocols = {"http", "https"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $sector_identifier_uri;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=20, nullable=false, options={"default" : "pairwise"})
     */
    private $subject_type = 'pairwise';

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $id_token_signed_response_alg;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $id_token_encrypted_response_alg;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $id_token_encrypted_response_enc;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $userinfo_signed_response_alg;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $userinfo_encrypted_response_alg;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $userinfo_encrypted_response_enc;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $request_object_signing_alg;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $request_object_encryption_alg;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $request_object_encryption_enc;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $token_endpoint_auth_method;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $token_endpoint_auth_signing_alg;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="integer")
     * @ORM\Column(type="integer", nullable=true)
     */
    private $default_max_age;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="boolean")
     */
    private $require_auth_time = false;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="array")
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $default_acr_values;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Url(checkDNS = false)
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $initiate_login_uri;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\All({
     *      @Assert\Type("string"),
     *      @Assert\Url(checkDNS = false)
     * })
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $request_uris;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", nullable=true)
     */
    private $registration_access_token;

    /**
     * @var OrganizationInterface
     * @ORM\ManyToOne(targetEntity="LoginCidadao\OAuthBundle\Model\OrganizationInterface", inversedBy="clients")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $organization;

    /**
     * @JMS\Expose
     * @JMS\Groups({"client_metadata"})
     * @Assert\All({
     *      @Assert\Type("string"),
     *      @Assert\Url(checkDNS = false)
     * })
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $post_logout_redirect_uris;

    public function __construct()
    {
        $this->response_types = ['code'];
        $this->grant_types = ['authorization_code'];
        $this->application_type = 'web';
        $this->require_auth_time = false;
        $this->subject_type = 'pairwise';
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string[]
     */
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
        $owners = [];
        if ($this->getClient()) {
            $owners = array_map(
                function (PersonInterface $owner) {
                    return $owner->getEmail();
                },
                $this->getClient()->getOwners()->toArray()
            );
        }
        $contacts = $this->contacts ?? [];

        return array_unique(array_merge($contacts, $owners));
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

    /**
     * @return string|null
     */
    public function getSubjectType()
    {
        return $this->subject_type;
    }

    /**
     * @param string $subject_type
     * @return ClientMetadata
     */
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

    /**
     * @param $default_acr_values
     * @return ClientMetadata
     */
    public function setDefaultAcrValues($default_acr_values)
    {
        $this->default_acr_values = $default_acr_values;

        return $this;
    }

    public function getInitiateLoginUri()
    {
        return $this->initiate_login_uri;
    }

    /**
     * @param $initiate_login_uri
     * @return ClientMetadata
     */
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

    /**
     * @JMS\Groups({"client_metadata"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("client_id")
     */
    public function getClientId()
    {
        if ($this->client_id === null && $this->client) {
            return $this->client->getClientId();
        }

        return $this->client_id;
    }

    public function setClientId($client_id)
    {
        $this->client_id = $client_id;

        return $this;
    }

    /**
     * @JMS\Groups({"client_metadata"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("client_secret")
     */
    public function getClientSecret()
    {
        if ($this->client_id === null && $this->client) {
            return $this->client->getClientSecret();
        }

        return $this->client_secret;
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
        $grantTypes = $this->getGrantTypes();
        $clientUri = $this->getClientUri();
        $tosUri = $this->getTosUri();
        $clientName = $this->getClientName();
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

        if (count($redirectUris) > 0) {
            $client->setRedirectUris($redirectUris);
        }

        $client->setVisible(false)
            ->setPublished(false);

        return $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function checkDefaults()
    {
        $this->enforceDefaultGrantTypes();
        $this->enforceDefaultResponseTypes();
        $this->enforceDefaultApplicationType();
        $this->enforceDefaultRequireAuthTime();
        $this->enforceDefaultIdTokenSignedResponseAlg();
        $this->enforceDefaultTokenEndpointAuthMethod();
        $this->enforceValidSubjectType();
    }

    private function enforceDefaultGrantTypes()
    {
        if (!$this->getGrantTypes()) {
            $this->setGrantTypes(['authorization_code']);
        }
    }

    private function enforceDefaultResponseTypes()
    {
        if (!$this->getResponseTypes()) {
            $this->setResponseTypes(['code']);
        }
    }

    private function enforceDefaultApplicationType()
    {
        if (!$this->getApplicationType()) {
            $this->setApplicationType('web');
        }
    }

    private function enforceDefaultRequireAuthTime()
    {
        if (!$this->getRequireAuthTime()) {
            $this->setRequireAuthTime(false);
        }
    }

    private function enforceDefaultIdTokenSignedResponseAlg()
    {
        if (!$this->getIdTokenSignedResponseAlg()) {
            $this->setIdTokenSignedResponseAlg('RS256');
        }
    }

    private function enforceDefaultTokenEndpointAuthMethod()
    {
        if (!$this->getTokenEndpointAuthMethod()) {
            $this->setTokenEndpointAuthMethod('client_secret_basic');
        }
    }

    private function enforceValidSubjectType()
    {
        if (false === array_search($this->getSubjectType(), ['public', 'pairwise'])) {
            $this->setSubjectType('pairwise');
        }
    }

    public function getSectorIdentifier()
    {
        $siUri = $this->getSectorIdentifierUri();
        if ($siUri) {
            $uri = $siUri;
        } else {
            $uris = $this->getRedirectUris();
            $uri = reset($uris);
        }

        return parse_url($uri, PHP_URL_HOST);
    }

    public function getRegistrationAccessToken()
    {
        return $this->registration_access_token;
    }

    /**
     * @param string $registration_access_token
     * @return ClientMetadata
     */
    public function setRegistrationAccessToken($registration_access_token)
    {
        $this->registration_access_token = $registration_access_token;

        return $this;
    }

    /**
     * @return OrganizationInterface
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param OrganizationInterface $organization
     */
    public function setOrganization($organization = null)
    {
        $this->organization = $organization;
    }

    /**
     * @return array
     */
    public function getPostLogoutRedirectUris()
    {
        return array_map(
            function ($value) {
                return self::canonicalizeUri($value);
            },
            $this->post_logout_redirect_uris ?? []
        );
    }

    /**
     * @param array
     * @return ClientMetadata
     */
    public function setPostLogoutRedirectUris($post_logout_redirect_uris)
    {
        $this->post_logout_redirect_uris = $post_logout_redirect_uris;

        return $this;
    }

    /**
     * Add trailing slashes
     * @param $uri
     * @return string
     */
    public static function canonicalizeUri($uri)
    {
        $parsed = parse_url($uri);
        if (array_key_exists('path', $parsed) === false) {
            $parsed['path'] = '/';
        }

        return self::unparseUrl($parsed);
    }

    private static function unparseUrl($parsed_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':'.$parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?'.$parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#'.$parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
