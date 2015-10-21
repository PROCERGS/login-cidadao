<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Storage;

use OAuth2\ServerBundle\Storage\AccessToken as BaseClass;
use OAuth2\Storage\AccessTokenInterface;
use Doctrine\ORM\EntityManager;

class AccessToken extends BaseClass implements AccessTokenInterface
{
    /** @var EntityManager */
    private $em;

    /** @var string */
    private $pairwiseSubjectIdSalt;

    public function __construct(EntityManager $EntityManager)
    {
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
     * @return
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
        $accessToken = $this->em->getRepository('PROCERGSOAuthBundle:AccessToken')
            ->findOneBy(array('token' => $oauth_token));

        if (!$accessToken) {
            return null;
        }

        // Get Client
        $client = $accessToken->getClient();

        return array(
            'client_id' => $client->getClientId(),
            'user_id' => $accessToken->getUserId($this->pairwiseSubjectIdSalt),
            'expires' => $accessToken->getExpiresAt(),
            'scope' => $accessToken->getScope(),
            'id_token' => $accessToken->getIdToken()
        );
    }

    /**
     * Store the supplied access token values to storage.
     *
     * We need to store access token data as we create and verify tokens.
     *
     * @param $oauth_token
     * oauth_token to be stored.
     * @param $client_id
     * Client identifier to be stored.
     * @param $user_id
     * User identifier to be stored.
     * @param int    $expires
     *                        Expiration to be stored as a Unix timestamp.
     * @param string $scope
     *                        (optional) Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_4
     */
    public function setAccessToken($oauth_token, $client_id, $user_id, $expires,
                                   $scope = null, $id_token = null)
    {
        // Get Client Entity
        $id     = explode('_', $client_id);
        $client = $this->em->getRepository('PROCERGSOAuthBundle:Client')
            ->find($id[0]);

        if (!$client) {
            return null;
        }

        if ($user_id === null) {
            return null;
        } else {
            $user = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Person')
                ->find($user_id);
        }

        // Create Access Token
        $accessToken = new \PROCERGS\OAuthBundle\Entity\AccessToken();
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

    public function setPairwiseSubjectIdSalt($pairwiseSubjectIdSalt)
    {
        $this->pairwiseSubjectIdSalt = $pairwiseSubjectIdSalt;
    }
}
