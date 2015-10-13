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

use OAuth2\ServerBundle\Storage\ClientCredentials as BaseClass;
use Doctrine\ORM\EntityManager;

class ClientCredentials extends BaseClass
{
    private $em;

    public function __construct(EntityManager $EntityManager)
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
     * @return
     * TRUE if the client credentials are valid, and MUST return FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-3.1
     *
     * @ingroup oauth2_section_3
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        // Get Client
        $id     = explode('_', $client_id);
        $client = $this->em->getRepository('PROCERGSOAuthBundle:Client')
            ->find($id[0]);

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
        // Get Client
        $id     = explode('_', $client_id);
        $client = $this->em->getRepository('PROCERGSOAuthBundle:Client')
            ->find($id[0]);

        if (!$client) {
            return false;
        }

        return array(
            'redirect_uri' => implode(' ', $client->getRedirectUris()),
            'client_id' => $client->getPublicId(),
            'grant_types' => $client->getAllowedGrantTypes()
        );
    }

    /**
     * Determine if the client is a "public" client, and therefore
     * does not require passing credentials for certain grant types
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return
     * TRUE if the client is public, and FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-2.3
     * @see https://github.com/bshaffer/oauth2-server-php/issues/257
     *
     * @ingroup oauth2_section_2
     */
    public function isPublicClient($client_id)
    {
        $id     = explode('_', $client_id);
        $client = $this->em->getRepository('PROCERGSOAuthBundle:Client')
            ->find($id[0]);

        if (!$client) {
            return false;
        }

        $secret = $client->getClientSecret();

        return empty($secret);
    }

    /**
     * Get the scope associated with this client
     *
     * @return
     * STRING the space-delineated scope list for the specified client_id
     */
    public function getClientScope($client_id)
    {
        // Get Client
        $id     = explode('_', $client_id);
        $client = $this->em->getRepository('PROCERGSOAuthBundle:Client')
            ->find($id[0]);

        if (!$client) {
            return false;
        }

        return implode(' ', $client->getAllowedScopes());
    }
}
