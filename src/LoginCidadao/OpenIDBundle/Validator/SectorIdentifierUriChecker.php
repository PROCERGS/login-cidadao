<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Validator;

use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;

class SectorIdentifierUriChecker
{
    /**
     * @param ClientMetadata $metadata
     * @param $sectorIdentifierUri
     * @return bool
     */
    public function check(ClientMetadata $metadata, $sectorIdentifierUri)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sectorIdentifierUri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $allowedUris = json_decode(trim(curl_exec($ch)));

        foreach ($metadata->getRedirectUris() as $uri) {
            if (array_search($uri, $allowedUris) === false) {
                return false;
            }
        }

        return true;
    }
}