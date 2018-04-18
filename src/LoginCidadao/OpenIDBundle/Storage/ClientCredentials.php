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

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaim;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use OAuth2\ServerBundle\Storage\ClientCredentials as BaseClass;

class ClientCredentials extends BaseClass
{
    private $em;

    public function __construct(EntityManagerInterface $EntityManager)
    {
        $this->em = $EntityManager;
    }

    /**
     * Make sure that the client credentials is valid.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $client_secret
     * (optional) If a secret is required, check that they've given the right one.
     *
     * @return TRUE if the client credentials are valid, and MUST return FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-3.1
     *
     * @ingroup oauth2_section_3
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        $client = $this->getClient($client_id);

        // If client exists check secret
        if ($client) {
            return $client->getClientSecret() === $client_secret;
        }

        return false;
    }

    /**
     * Get client details corresponding client_id.
     *
     * OAuth says we should store request URIs for each registered client.
     * Implement this function to grab the stored URI for a given client id.
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return array
     *               Client details. The only mandatory key in the array is "redirect_uri".
     *               This function MUST return FALSE if the given client does not exist or is
     *               invalid. "redirect_uri" can be space-delimited to allow for multiple valid uris.
     * @code
     *               return array(
     *               "redirect_uri" => REDIRECT_URI,      // REQUIRED redirect_uri registered for the client
     *               "client_id"    => CLIENT_ID,         // OPTIONAL the client id
     *               "grant_types"  => GRANT_TYPES,       // OPTIONAL an array of restricted grant types
     *               );
     * @endcode
     *
     * @ingroup oauth2_section_4
     */
    public function getClientDetails($client_id)
    {
        $client = $this->getClient($client_id);

        if (!$client) {
            return false;
        }

        return [
            'redirect_uri' => implode(' ', $client->getRedirectUris()),
            'client_id' => $client->getPublicId(),
            'grant_types' => $client->getAllowedGrantTypes(),
        ];
    }

    /**
     * Determine if the client is a "public" client, and therefore
     * does not require passing credentials for certain grant types
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return TRUE if the client is public, and FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-2.3
     * @see https://github.com/bshaffer/oauth2-server-php/issues/257
     *
     * @ingroup oauth2_section_2
     */
    public function isPublicClient($client_id)
    {
        $client = $this->getClient($client_id);

        if (!$client) {
            return false;
        }

        $secret = $client->getClientSecret();

        return empty($secret);
    }

    /**
     * Get the scope associated with this client
     *
     * @return string the space-delineated scope list for the specified client_id
     */
    public function getClientScope($client_id)
    {
        /** @var Client $client */
        $client = $this->getClient($client_id);

        if (!$client instanceof ClientInterface) {
            return false;
        }

        /*
         * TODO: performance issue: if there are too many Remote Claims listing all of them might be an issue
         */
        $remoteClaims = $this->getRemoteClaimsTags($this->getAllRemoteClaims());
        $allowedScopes = array_merge($client->getAllowedScopes(), $remoteClaims);

        return implode(' ', $allowedScopes);
    }

    /**
     * @param $client_id mixed
     * @return ClientInterface|null
     */
    private function getClient($client_id)
    {
        $randomId = null;
        if (strstr($client_id, '_') !== false) {
            $parts = explode('_', $client_id);
            $client_id = $parts[0];
            $randomId = $parts[1];
        }

        /** @var ClientRepository $repo */
        $repo = $this->em->getRepository('LoginCidadaoOAuthBundle:Client');

        if ($randomId) {
            /** @var ClientInterface|null $client */
            $client = $repo->findOneBy([
                'id' => $client_id,
                'randomId' => $randomId,
            ]);
        } else {
            /** @var ClientInterface|null $client */
            $client = $repo->find($client_id);
        }

        return $client;
    }

    /**
     * @return array|RemoteClaimInterface[]
     */
    private function getAllRemoteClaims()
    {
        /** @var RemoteClaimRepository $repo */
        $repo = $this->em->getRepository('LoginCidadaoRemoteClaimsBundle:RemoteClaim');

        $remoteClaims = $repo->findAll();

        return $remoteClaims;
    }

    private function getRemoteClaimsTags(array $remoteClaims)
    {
        if (count($remoteClaims) > 0) {
            return array_map(function (RemoteClaimInterface $claim) {
                return $claim->getName();
            }, $remoteClaims);
        }

        return [];
    }
}
