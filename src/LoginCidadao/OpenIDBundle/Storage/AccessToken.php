<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Storage;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use OAuth2\ServerBundle\Storage\AccessToken as BaseClass;
use OAuth2\Storage\AccessTokenInterface;
use Doctrine\ORM\EntityManager;

class AccessToken extends BaseClass implements AccessTokenInterface
{
    /** @var EntityManager */
    private $em;

    /** @var SubjectIdentifierService */
    private $subjectIdentifierService;

    public function __construct(EntityManager $EntityManager)
    {
        parent::__construct($EntityManager);
        $this->em = $EntityManager;
    }

    /**
     * Look up the supplied oauth_token from storage.
     *
     * We need to retrieve access token data as we create and verify tokens.
     *
     * @param $oauth_token
     * oauth_token to be check with.
     *
     * @return array|null
     * An associative array as below, and return NULL if the supplied oauth_token
     * is invalid:
     * - client_id: Stored client identifier.
     * - expires: Stored expiration in unix timestamp.
     * - scope: (optional) Stored scope values in space-separated string.
     *
     * @ingroup oauth2_section_7
     */
    public function getAccessToken($oauth_token)
    {
        /** @var \LoginCidadao\OAuthBundle\Entity\AccessToken $accessToken */
        $accessToken = $this->em->getRepository('LoginCidadaoOAuthBundle:AccessToken')
            ->findOneBy(['token' => $oauth_token]);

        if (!$accessToken) {
            return null;
        }

        /** @var Client $client */
        $client = $accessToken->getClient();

        /** @var PersonInterface $person */
        $person = $accessToken->getUser();

        return [
            'client_id' => $client->getClientId(),
            'user_id' => $this->subjectIdentifierService->getSubjectIdentifier($person, $client),
            'expires' => $accessToken->getExpiresAt(),
            'scope' => $accessToken->getScope(),
            'id_token' => $accessToken->getIdToken(),
        ];
    }

    /**
     * Store the supplied access token values to storage.
     *
     * We need to store access token data as we create and verify tokens.
     *
     * @param string $oauth_token
     * oauth_token to be stored.
     * @param string $client_id
     * Client identifier to be stored.
     * @param string $user_id
     * User identifier to be stored.
     * @param int $expires Expiration to be stored as a Unix timestamp.
     * @param string $scope (optional) Scopes to be stored in space-separated string.
     * @param null|string $id_token
     * @return null|void
     * @ingroup oauth2_section_4
     */
    public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = null, $id_token = null)
    {
        // Get Client Entity
        $id = explode('_', $client_id);

        /** @var ClientInterface $client */
        $client = $this->em->getRepository('LoginCidadaoOAuthBundle:Client')->find($id[0]);

        if (!$client) {
            return null;
        }

        if ($user_id === null) {
            return null;
        } else {
            /** @var PersonInterface $user */
            $user = $this->em->getRepository('LoginCidadaoCoreBundle:Person')->find($user_id);
        }

        // Create Access Token
        $accessToken = new \LoginCidadao\OAuthBundle\Entity\AccessToken();
        $accessToken->setToken($oauth_token);
        $accessToken->setClient($client);
        $accessToken->setUser($user);
        $accessToken->setExpiresAt($expires);
        $accessToken->setScope($scope);
        $accessToken->setIdToken($id_token);

        // Store Access Token and Authorization
        $this->em->persist($accessToken);
        $this->em->flush();
    }

    public function setSubjectIdentifierService(SubjectIdentifierService $subjectIdentifierService)
    {
        $this->subjectIdentifierService = $subjectIdentifierService;
    }
}
