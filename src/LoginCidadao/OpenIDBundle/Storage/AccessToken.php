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
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use OAuth2\ServerBundle\Storage\AccessToken as BaseClass;
use OAuth2\Storage\AccessTokenInterface;
use Doctrine\ORM\EntityManager;

class AccessToken extends BaseClass implements AccessTokenInterface
{
    /** @var EntityManager */
    private $em;

    /** @var ClientManager */
    private $clientManager;

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
        $accessToken = $this->em->getRepository('LoginCidadaoOAuthBundle:AccessToken')
            ->findOneBy(['token' => $oauth_token]);

        if (!$accessToken instanceof \LoginCidadao\OAuthBundle\Entity\AccessToken) {
            return null;
        }

        /** @var Client $client */
        $client = $accessToken->getClient();

        /** @var PersonInterface $person */
        $person = $accessToken->getUser();

        return [
            'client_id' => $client->getClientId(),
            'user_id' => $this->subjectIdentifierService->getSubjectIdentifier($person, $client->getMetadata()),
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
     * @param string|null $user_id
     * User identifier to be stored.
     * @param int $expires Expiration to be stored as a Unix timestamp.
     * @param string $scope (optional) Scopes to be stored in space-separated string.
     * @param null|string $id_token
     * @return null|void
     * @ingroup oauth2_section_4
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setAccessToken($oauth_token, $client_id, $user_id = null, $expires, $scope = null, $id_token = null)
    {
        $user = null;
        if (!$client = $this->clientManager->getClientById($client_id)) {
            return null;
        } elseif ($user_id !== null) {
            $user = $this->getUser($client, $user_id);
        }

        // Create Access Token
        $accessToken = new \LoginCidadao\OAuthBundle\Entity\AccessToken();
        $accessToken->setToken($oauth_token);
        $accessToken->setClient($client);
        if ($user !== null) {
            $accessToken->setUser($user);
        }
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

    public function setClientManager(ClientManager $clientManager)
    {
        $this->clientManager = $clientManager;
    }

    /**
     * @param ClientInterface $client
     * @param $user_id
     * @return PersonInterface|null|object
     */
    private function getUser(ClientInterface $client, $user_id)
    {
        $user = $this->subjectIdentifierService->getPerson($user_id, $client);
        if (!$user instanceof PersonInterface) {
            $user = $this->em->getRepository('LoginCidadaoCoreBundle:Person')->find($user_id);
        }

        return $user;
    }
}
