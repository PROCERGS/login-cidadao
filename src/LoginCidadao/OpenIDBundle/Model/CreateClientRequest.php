<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CreateClientRequest
{
    /**
     * @var string[]
     *
     * @Assert\Type("array")
     * @Assert\NotBlank()
     * @Assert\All({
     *      @Assert\Type(type="string"),
     *      @Assert\NotBlank,
     *      @Assert\Url(checkDNS = false)
     * })
     */
    public $redirect_uris = [];

    /**
     * @var array
     *
     * @Assert\Type("array")
     * @Assert\All({
     *      @Assert\Type("string")
     * })
     */
    public $response_types = ['code'];

    /**
     * @var array
     *
     * @Assert\Type("array")
     * @Assert\Choice({"authorization_code", "implicit", "refresh_token"}, multiple=true)
     * @Assert\All({
     *      @Assert\Type("string")
     * })
     */
    public $grant_types = ['authorization_code'];

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice({"web", "native"})
     */
    public $application_type = 'web';

    /**
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type("string"),
     *     @Assert\Email()
     * })
     */
    public $contacts = [];

    /**
     * @Assert\Type(type="string")
     */
    public $client_name;

    /**
     * @Assert\Type(type="string")
     * @Assert\Url(checkDNS = false)
     */
    public $logo_uri;

    /**
     * @Assert\Type(type="string")
     * @Assert\Url(checkDNS = false)
     */
    public $client_uri;

    /**
     * @Assert\Type(type="string")
     * @Assert\Url(checkDNS = false)
     */
    public $policy_uri;

    /**
     * @Assert\Url(checkDNS = false)
     * @Assert\Type(type="string")
     */
    public $tos_uri;

    /**
     * @Assert\Url(checkDNS = false)
     * @Assert\Type(type="string")
     */
    public $jwks_uri;

    /**
     * @Assert\Type(type="string")
     */
    public $jwks;

    /**
     * @Assert\Url(checkDNS = false, protocols = {"http", "https"})
     * @Assert\Type(type="string")
     */
    public $sector_identifier_uri;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice({"pairwise"})
     */
    public $subject_type = 'pairwise';

    /**
     * @Assert\Type(type="string")
     */
    public $id_token_signed_response_alg;

    /**
     * @Assert\Type(type="string")
     */
    public $id_token_encrypted_response_alg;

    /**
     * @Assert\Type(type="string")
     */
    public $id_token_encrypted_response_enc;

    /**
     * @Assert\Type(type="string")
     */
    public $userinfo_signed_response_alg;

    /**
     * @Assert\Type(type="string")
     */
    public $userinfo_encrypted_response_alg;

    /**
     * @Assert\Type(type="string")
     */
    public $userinfo_encrypted_response_enc;

    /**
     * @Assert\Type(type="string")
     */
    public $request_object_signing_alg;

    /**
     * @Assert\Type(type="string")
     */
    public $request_object_encryption_alg;

    /**
     * @Assert\Type(type="string")
     */
    public $request_object_encryption_enc;

    /**
     * @Assert\Type(type="string")
     */
    public $token_endpoint_auth_method;

    /**
     * @Assert\Type(type="string")
     */
    public $token_endpoint_auth_signing_alg;

    /**
     * @Assert\Type(type="integer")
     */
    public $default_max_age;

    /**
     * @Assert\Type(type="boolean")
     */
    public $require_auth_time = false;

    /**
     * @Assert\Type(type="array")
     */
    public $default_acr_values = [];

    /**
     * @Assert\Url(checkDNS = false)
     * @Assert\Type(type="string")
     */
    public $initiate_login_uri;

    /**
     * @Assert\Type("array")
     * @Assert\All({
     *      @Assert\Type("string"),
     *      @Assert\Url(checkDNS = false)
     * })
     */
    public $request_uris = [];

    /**
     * @Assert\Type(type="string")
     */
    public $registration_access_token;

    /**
     * @Assert\Type("array")
     * @Assert\All({
     *      @Assert\Type("string"),
     *      @Assert\Url(checkDNS = false)
     * })
     */
    public $post_logout_redirect_uris = [];
}
