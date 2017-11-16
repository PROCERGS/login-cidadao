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
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use OAuth2\ServerBundle\Storage\RefreshToken as BaseClass;
use OAuth2\Storage\RefreshTokenInterface;
use Doctrine\ORM\EntityManager;

class RefreshToken extends BaseClass implements RefreshTokenInterface
{
    private $em;

    public function __construct(EntityManager $EntityManager)
    {
        parent::__construct($EntityManager);
        $this->em = $EntityManager;
    }

    /**
     * Grant refresh access tokens.
     *
     * Retrieve the stored data for the given refresh token.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be check with.
     *
     * @return array
     * An associative array as below, and NULL if the refresh_token is
     * invalid:
     * - refresh_token: Stored refresh token identifier.
     * - client_id: Stored client identifier.
     * - user_id: Stored user identifier.
     * - expires: Stored expiration unix timestamp.
     * - scope: (optional) Stored scope values in space-separated string.
     *
     * @see http://tools.ietf.org/html/rfc6749#section-6
     *
     * @ingroup oauth2_section_6
     */
    public function getRefreshToken($refresh_token)
    {
        /** @var \LoginCidadao\OAuthBundle\Entity\RefreshToken $refreshToken */
        $refreshToken = $this->em->getRepository('LoginCidadaoOAuthBundle:RefreshToken')
            ->findOneBy(array('token' => $refresh_token));

        if (!$refreshToken) {
            return null;
        }

        // Get Client
        /** @var ClientInterface $client */
        $client = $refreshToken->getClient();

        /** @var PersonInterface $user */
        $user = $refreshToken->getUser();

        return [
            'refresh_token' => $refreshToken->getToken(),
            'client_id' => $client->getPublicId(),
            'user_id' => $user->getId(),
            'expires' => $refreshToken->getExpiresAt(),
            'scope' => $refreshToken->getScope(),
        ];
    }

    /**
     * Take the provided refresh token values and store them somewhere.
     *
     * This function should be the storage counterpart to getRefreshToken().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be stored.
     * @param $client_id
     * Client identifier to be stored.
     * @param $user_id
     * User identifier to be stored.
     * @param $expires
     * expires to be stored.
     * @param $scope
     * (optional) Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_6
     * @return null|void
     */
    public function setRefreshToken(
        $refresh_token,
        $client_id,
        $user_id,
        $expires,
        $scope = null
    ) {
        // Get Client Entity
        $id = explode('_', $client_id);

        /** @var ClientInterface $client */
        $client = $this->em->getRepository('LoginCidadaoOAuthBundle:Client')
            ->find($id[0]);

        if (!$client) {
            return null;
        }

        if ($user_id === null) {
            return null;
        } else {
            /** @var PersonInterface $user */
            $user = $this->em->getRepository('LoginCidadaoCoreBundle:Person')
                ->find($user_id);
        }

        // Create Refresh Token
        $refreshToken = new \LoginCidadao\OAuthBundle\Entity\RefreshToken();
        $refreshToken->setToken($refresh_token);
        $refreshToken->setClient($client);
        $refreshToken->setUser($user);
        $refreshToken->setExpiresAt($expires);
        $refreshToken->setScope($scope);

        // Store Refresh Token
        $this->em->persist($refreshToken);
        $this->em->flush();
    }

    /**
     * Expire a used refresh token.
     *
     * This is not explicitly required in the spec, but is almost implied.
     * After granting a new refresh token, the old one is no longer useful and
     * so should be forcibly expired in the data store so it can't be used again.
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * @param $refresh_token
     * Refresh token to be expirse.
     *
     * @ingroup oauth2_section_6
     */
    public function unsetRefreshToken($refresh_token)
    {
        $refreshToken = $this->em->getRepository('LoginCidadaoOAuthBundle:RefreshToken')
            ->findOneBy(['token' => $refresh_token]);
        $this->em->remove($refreshToken);
        $this->em->flush();
    }
}
